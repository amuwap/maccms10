<?php
namespace app\common\util;

use think\Cache;
use think\Db;
use app\common\model\Live as LiveModel;

class LiveService
{
    protected static $cachePrefix = 'live_service_';
    protected static $cacheTime = 3600;

    public static function getLiveList($status = null, $page = 1, $limit = 20, $order = 'live_sort asc, live_time desc', $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'list_' . ($status === null ? 'all' : $status) . '_' . $page . '_' . $limit . '_' . md5($order);
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $query = LiveModel::where('1=1');
        
        if ($status !== null) {
            $query->where('live_status', $status);
        }

        $list = $query->order($order)->page($page, $limit)->select();
        $total = $query->count();

        $result = [
            'code' => 1,
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];

        if ($use_cache && !empty($list)) {
            Cache::set($cacheKey, $result, 600);
        }

        return $result;
    }

    public static function getLiveDetail($live_id, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'detail_' . $live_id;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $live = LiveModel::get($live_id);
        
        if ($live && $use_cache) {
            Cache::set($cacheKey, $live, self::$cacheTime);
        }

        return $live;
    }

    public static function updateLiveStatus($live_id, $status)
    {
        Db::startTrans();
        try {
            $live = LiveModel::get($live_id);
            if (!$live) {
                Db::rollback();
                return ['code' => 0, 'msg' => '直播不存在'];
            }

            LiveModel::update([
                'live_status' => $status,
                'live_update_time' => time()
            ], ['live_id' => $live_id]);

            self::clearLiveCache($live_id);
            Db::commit();
            return ['code' => 1, 'msg' => '状态更新成功'];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '状态更新失败: ' . $e->getMessage()];
        }
    }

    public static function updateLiveViewerCount($live_id, $delta = 1)
    {
        Db::startTrans();
        try {
            $live = LiveModel::get($live_id);
            if (!$live) {
                Db::rollback();
                return false;
            }

            $newCount = max(0, $live['live_viewers'] + $delta);
            
            LiveModel::update([
                'live_viewers' => $newCount,
                'live_update_time' => time()
            ], ['live_id' => $live_id]);

            self::clearLiveCache($live_id);
            Db::commit();
            return $newCount;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    public static function createLive($data)
    {
        Db::startTrans();
        try {
            $data['live_time'] = time();
            $data['live_update_time'] = time();
            
            $live = LiveModel::create($data);
            
            Db::commit();
            self::clearCache();
            return ['code' => 1, 'msg' => '创建成功', 'data' => $live];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '创建失败: ' . $e->getMessage()];
        }
    }

    public static function updateLive($live_id, $data)
    {
        Db::startTrans();
        try {
            $live = LiveModel::get($live_id);
            if (!$live) {
                Db::rollback();
                return ['code' => 0, 'msg' => '直播不存在'];
            }

            $data['live_update_time'] = time();
            LiveModel::update($data, ['live_id' => $live_id]);

            Db::commit();
            self::clearLiveCache($live_id);
            return ['code' => 1, 'msg' => '更新成功'];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '更新失败: ' . $e->getMessage()];
        }
    }

    public static function deleteLive($live_id)
    {
        Db::startTrans();
        try {
            $live = LiveModel::get($live_id);
            if (!$live) {
                Db::rollback();
                return ['code' => 0, 'msg' => '直播不存在'];
            }

            LiveModel::destroy($live_id);
            
            Db::commit();
            self::clearLiveCache($live_id);
            return ['code' => 1, 'msg' => '删除成功'];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '删除失败: ' . $e->getMessage()];
        }
    }

    public static function getOnlineLives($page = 1, $limit = 20, $use_cache = true)
    {
        return self::getLiveList(1, $page, $limit, 'live_viewers desc, live_sort asc', $use_cache);
    }

    public static function getHotLives($type_id = 0, $limit = 10, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'hot_' . $type_id . '_' . $limit;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $query = LiveModel::where('live_status', 1);
        if ($type_id > 0) {
            $query->where('type_id', $type_id);
        }

        $result = $query->order('live_viewers desc')->limit($limit)->select();

        if ($use_cache && !empty($result)) {
            Cache::set($cacheKey, $result, 600);
        }

        return $result;
    }

    public static function getLiveStatistics($start_time = 0, $end_time = 0)
    {
        $query = LiveModel::where('1=1');

        if ($start_time > 0) {
            $query->where('live_time', '>=', $start_time);
        }

        if ($end_time > 0) {
            $query->where('live_time', '<=', $end_time);
        }

        $totalLives = $query->count();
        $totalViewers = $query->sum('live_viewers');
        $onlineLives = LiveModel::where('live_status', 1)->count();

        return [
            'code' => 1,
            'data' => [
                'total_lives' => $totalLives,
                'total_viewers' => $totalViewers,
                'online_lives' => $onlineLives
            ]
        ];
    }

    public static function batchUpdateLiveStatus($live_ids, $status)
    {
        if (empty($live_ids)) {
            return ['code' => 1, 'msg' => '没有选择直播'];
        }

        Db::startTrans();
        try {
            foreach ($live_ids as $live_id) {
                self::updateLiveStatus($live_id, $status);
            }
            Db::commit();
            self::clearCache();
            return ['code' => 1, 'msg' => '批量更新成功'];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '批量更新失败: ' . $e->getMessage()];
        }
    }

    protected static function clearLiveCache($live_id)
    {
        $patterns = [
            self::$cachePrefix . 'detail_' . $live_id,
            self::$cachePrefix . 'list_*'
        ];

        foreach ($patterns as $pattern) {
            Cache::clear($pattern);
        }
    }

    public static function clearCache($type = null)
    {
        if ($type) {
            Cache::clear(self::$cachePrefix . '*' . $type . '*');
        } else {
            Cache::clear(self::$cachePrefix . '*');
        }
        return ['code' => 1, 'msg' => '缓存清除成功'];
    }

    public static function checkLiveStream($live_id)
    {
        $live = self::getLiveDetail($live_id, false);
        if (!$live) {
            return ['code' => 0, 'msg' => '直播不存在'];
        }

        $streamUrl = $live['live_url'];
        if (empty($streamUrl)) {
            return ['code' => 0, 'msg' => '直播地址为空'];
        }

        $isOnline = self::checkUrlStatus($streamUrl);
        
        if ($isOnline && $live['live_status'] != 1) {
            self::updateLiveStatus($live_id, 1);
        } elseif (!$isOnline && $live['live_status'] == 1) {
            self::updateLiveStatus($live_id, 0);
        }

        return ['code' => 1, 'is_online' => $isOnline, 'status' => $live['live_status']];
    }

    protected static function checkUrlStatus($url, $timeout = 5)
    {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode >= 200 && $httpCode < 400;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function createFakeLive($data)
    {
        Db::startTrans();
        try {
            $data['live_time'] = time();
            $data['live_update_time'] = time();
            $data['live_is_fake'] = 1;
            $data['live_status'] = 1;
            
            $live = LiveModel::create($data);
            
            Db::commit();
            self::clearCache();
            return ['code' => 1, 'msg' => '创建成功', 'data' => $live];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '创建失败: ' . $e->getMessage()];
        }
    }

    public static function getFakeLiveStreamUrl($live_id)
    {
        $live = self::getLiveDetail($live_id, false);
        if (!$live) {
            return ['code' => 0, 'msg' => '直播不存在'];
        }

        if (empty($live['live_video_url'])) {
            return ['code' => 0, 'msg' => '视频地址为空'];
        }

        $streamUrl = self::generateFakeStreamUrl($live['live_video_url'], $live_id);
        
        return ['code' => 1, 'url' => $streamUrl];
    }

    protected static function generateFakeStreamUrl($videoUrl, $liveId)
    {
        $encodedUrl = urlencode($videoUrl);
        return '/live/stream?live_id=' . $liveId . '&video_url=' . $encodedUrl;
    }

    public static function simulateViewerInteraction($live_id, $interaction_type = 'view')
    {
        switch ($interaction_type) {
            case 'view':
                self::updateLiveViewerCount($live_id, 1);
                break;
            case 'comment':
                self::addFakeComment($live_id);
                break;
            case 'like':
                self::updateLiveLikeCount($live_id, 1);
                break;
        }
        return ['code' => 1, 'msg' => '互动模拟成功'];
    }

    protected static function addFakeComment($live_id)
    {
        $comments = [
            '这个直播真好看！',
            '主播好厉害',
            '太精彩了',
            '支持支持',
            '这个内容很有价值',
            '主播辛苦了',
            '期待更多内容',
            '非常喜欢这个直播',
            '内容质量很高',
            '主播讲解得很详细'
        ];

        $comment = $comments[array_rand($comments)];
        $username = '观众' . mt_rand(1000, 9999);

        $cacheKey = self::$cachePrefix . 'comments_' . $live_id;
        $comments = Cache::get($cacheKey, []);
        
        $comments[] = [
            'username' => $username,
            'content' => $comment,
            'time' => time()
        ];

        if (count($comments) > 50) {
            array_shift($comments);
        }

        Cache::set($cacheKey, $comments, 3600);
    }

    public static function getFakeComments($live_id, $limit = 20)
    {
        $cacheKey = self::$cachePrefix . 'comments_' . $live_id;
        $comments = Cache::get($cacheKey, []);
        
        return array_slice(array_reverse($comments), 0, $limit);
    }

    public static function updateLiveLikeCount($live_id, $delta = 1)
    {
        Db::startTrans();
        try {
            $live = LiveModel::get($live_id);
            if (!$live) {
                Db::rollback();
                return false;
            }

            $newCount = max(0, $live['live_likes'] + $delta);
            
            LiveModel::update([
                'live_likes' => $newCount,
                'live_update_time' => time()
            ], ['live_id' => $live_id]);

            self::clearLiveCache($live_id);
            Db::commit();
            return $newCount;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    public static function getFakeLiveStatistics($live_id)
    {
        $live = self::getLiveDetail($live_id, false);
        if (!$live) {
            return ['code' => 0, 'msg' => '直播不存在'];
        }

        $cacheKey = self::$cachePrefix . 'stats_' . $live_id;
        $stats = Cache::get($cacheKey, [
            'viewers' => mt_rand(50, 200),
            'likes' => mt_rand(20, 100),
            'comments' => mt_rand(5, 30)
        ]);

        $stats['viewers'] += mt_rand(0, 5);
        $stats['likes'] += mt_rand(0, 2);

        Cache::set($cacheKey, $stats, 300);

        return [
            'code' => 1,
            'data' => [
                'viewers' => $stats['viewers'],
                'likes' => $stats['likes'],
                'comments' => $stats['comments'],
                'live_time' => $live['live_time']
            ]
        ];
    }

    public static function startFakeLive($live_id)
    {
        $live = self::getLiveDetail($live_id, false);
        if (!$live) {
            return ['code' => 0, 'msg' => '直播不存在'];
        }

        if (empty($live['live_video_url'])) {
            return ['code' => 0, 'msg' => '视频地址为空'];
        }

        $result = self::updateLiveStatus($live_id, 1);
        if ($result['code'] == 1) {
            self::startAutoInteraction($live_id);
        }

        return $result;
    }

    public static function stopFakeLive($live_id)
    {
        return self::updateLiveStatus($live_id, 0);
    }

    protected static function startAutoInteraction($live_id)
    {
        $cacheKey = self::$cachePrefix . 'auto_interaction_' . $live_id;
        
        if (!Cache::get($cacheKey)) {
            Cache::set($cacheKey, 1, 86400);
            
            orkerman\Worker::runAll();
        }
    }

    public static function getFakeLives($page = 1, $limit = 20)
    {
        $query = LiveModel::where('live_is_fake', 1);
        $list = $query->order('live_time desc')->page($page, $limit)->select();
        $total = $query->count();

        return [
            'code' => 1,
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    }
}