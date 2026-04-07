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
}
