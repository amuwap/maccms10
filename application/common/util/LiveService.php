<?php
namespace app\common\util;

use think\Cache;
use think\Db;
use app\common\model\Live as LiveModel;

class LiveService
{
    protected static $cachePrefix = 'live_service_';
    protected static $cacheTime = 3600;

    protected static $viewerNames = [
        '小明', '小红', '小刚', '小美', '小华', '小伟', '小丽', '小强', '小敏', '小磊',
        '阳光少年', '快乐女孩', '追梦人', '幸福时光', '美好明天', '快乐人生', '幸福天使', '快乐精灵',
        '清风拂面', '雨后彩虹', '春暖花开', '夏日清凉', '秋叶飘零', '冬雪纷飞', '四季如歌'
    ];

    protected static $virtualGifts = [
        ['id' => 1, 'name' => '鲜花', 'price' => 1, 'icon' => '🌹'],
        ['id' => 2, 'name' => '掌声', 'price' => 1, 'icon' => '👏'],
        ['id' => 3, 'name' => '爱心', 'price' => 5, 'icon' => '❤️'],
        ['id' => 4, 'name' => '火箭', 'price' => 10, 'icon' => '🚀'],
        ['id' => 5, 'name' => '钻石', 'price' => 20, 'icon' => '💎'],
        ['id' => 6, 'name' => '皇冠', 'price' => 50, 'icon' => '👑'],
        ['id' => 7, 'name' => '城堡', 'price' => 100, 'icon' => '🏰'],
        ['id' => 8, 'name' => '超级火箭', 'price' => 200, 'icon' => '🚀✨']
    ];

    protected static $danmakuContents = [
        '666', '好看', '主播好', '太棒了', '继续加油', '支持你',
        '学到了', '感谢分享', '很有价值', '内容很棒', '主播辛苦了',
        '期待更多', '非常喜欢', '内容质量高', '讲解详细', '很有帮助',
        '厉害了', '太强了', '佩服佩服', '666666', '牛啊牛啊',
        '学到很多', '干货满满', '感谢主播', '必须支持', '已关注'
    ];

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

    public static function sendVirtualGift($live_id, $gift_id, $user_id = 0, $count = 1)
    {
        $gift = self::getVirtualGiftById($gift_id);
        if (!$gift) {
            return ['code' => 0, 'msg' => '礼物不存在'];
        }

        $username = $user_id > 0 ? '用户' . $user_id : self::$viewerNames[array_rand(self::$viewerNames)];
        
        $giftData = [
            'gift_id' => $gift['id'],
            'gift_name' => $gift['name'],
            'gift_icon' => $gift['icon'],
            'gift_price' => $gift['price'],
            'count' => $count,
            'username' => $username,
            'user_id' => $user_id,
            'time' => time()
        ];

        $cacheKey = self::$cachePrefix . 'gifts_' . $live_id;
        $gifts = Cache::get($cacheKey, []);
        array_unshift($gifts, $giftData);
        
        if (count($gifts) > 50) {
            array_pop($gifts);
        }
        
        Cache::set($cacheKey, $gifts, 3600);

        self::updateLiveGiftCount($live_id, $gift['price'] * $count);

        return ['code' => 1, 'msg' => '礼物发送成功', 'data' => $giftData];
    }

    public static function getVirtualGiftById($gift_id)
    {
        foreach (self::$virtualGifts as $gift) {
            if ($gift['id'] == $gift_id) {
                return $gift;
            }
        }
        return null;
    }

    public static function getVirtualGifts()
    {
        return ['code' => 1, 'data' => self::$virtualGifts];
    }

    public static function getLiveGifts($live_id, $limit = 20)
    {
        $cacheKey = self::$cachePrefix . 'gifts_' . $live_id;
        $gifts = Cache::get($cacheKey, []);
        return array_slice($gifts, 0, $limit);
    }

    protected static function updateLiveGiftCount($live_id, $amount)
    {
        Db::startTrans();
        try {
            $live = LiveModel::get($live_id);
            if (!$live) {
                Db::rollback();
                return false;
            }

            $newCount = ($live['live_gifts'] ?? 0) + $amount;
            
            LiveModel::update([
                'live_gifts' => $newCount,
                'live_update_time' => time()
            ], ['live_id' => $live_id]);

            self::clearLiveCache($live_id);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    public static function sendDanmaku($live_id, $content, $user_id = 0)
    {
        $username = $user_id > 0 ? '用户' . $user_id : self::$viewerNames[array_rand(self::$viewerNames)];
        
        $danmakuData = [
            'content' => $content,
            'username' => $username,
            'user_id' => $user_id,
            'time' => time(),
            'color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF))
        ];

        $cacheKey = self::$cachePrefix . 'danmaku_' . $live_id;
        $danmakus = Cache::get($cacheKey, []);
        array_push($danmakus, $danmakuData);
        
        if (count($danmakus) > 100) {
            array_shift($danmakus);
        }
        
        Cache::set($cacheKey, $danmakus, 3600);

        return ['code' => 1, 'msg' => '弹幕发送成功', 'data' => $danmakuData];
    }

    public static function getDanmakus($live_id, $limit = 50)
    {
        $cacheKey = self::$cachePrefix . 'danmaku_' . $live_id;
        $danmakus = Cache::get($cacheKey, []);
        return array_slice(array_reverse($danmakus), 0, $limit);
    }

    public static function simulateAutoInteraction($live_id)
    {
        // 获取直播信息
        $live = self::getLiveDetail($live_id, false);
        if (!$live) {
            return ['code' => 0, 'msg' => '直播不存在'];
        }

        // 基于直播时长调整互动策略
        $liveDuration = time() - $live['live_time'];
        $liveContent = $live['live_title'] . ' ' . ($live['live_content'] ?? '');
        $interactionProbabilities = self::getInteractionProbabilities($liveDuration, $live['live_viewers'], $liveContent);
        
        $type = self::weightedRandom($interactionProbabilities);
        
        switch ($type) {
            case 'view':
                $viewerCount = mt_rand(1, 3);
                self::updateLiveViewerCount($live_id, $viewerCount);
                break;
            case 'comment':
                $context = self::getLiveContext($live_id);
                self::addIntelligentComment($live_id, $context);
                break;
            case 'like':
                $likeCount = mt_rand(1, 5);
                self::updateLiveLikeCount($live_id, $likeCount);
                break;
            case 'gift':
                $gift = self::getAppropriateGift($liveDuration, $live['live_viewers'], $liveContent);
                self::sendVirtualGift($live_id, $gift['id']);
                break;
            case 'danmaku':
                $content = self::getIntelligentDanmaku($liveDuration, $live['live_viewers'], $liveContent);
                self::sendDanmaku($live_id, $content);
                break;
        }

        return ['code' => 1, 'msg' => '自动互动成功', 'type' => $type];
    }

    protected static function getInteractionProbabilities($duration, $viewerCount, $liveContent = '')
    {
        // 基础概率
        $probabilities = [
            'view' => 0.35,
            'comment' => 0.25,
            'like' => 0.2,
            'danmaku' => 0.15,
            'gift' => 0.05
        ];

        // 基于直播时长调整
        if ($duration < 300) { // 前5分钟
            $probabilities['view'] *= 1.8; // 增加观众进入
            $probabilities['comment'] *= 0.7; // 减少评论
            $probabilities['danmaku'] *= 0.8; // 减少弹幕
        } elseif ($duration > 1800) { // 30分钟后
            $probabilities['gift'] *= 1.5; // 增加送礼
            $probabilities['comment'] *= 1.2; // 增加评论
            $probabilities['danmaku'] *= 1.1; // 增加弹幕
        }

        // 基于观众数量调整
        if ($viewerCount > 100) {
            $probabilities['comment'] *= 1.3;
            $probabilities['danmaku'] *= 1.5;
            $probabilities['gift'] *= 1.2;
        } elseif ($viewerCount < 20) {
            $probabilities['view'] *= 1.5;
            $probabilities['like'] *= 1.2;
        }

        // 基于直播内容调整
        if (strpos($liveContent, '游戏') !== false) {
            $probabilities['danmaku'] *= 1.3;
            $probabilities['comment'] *= 1.2;
        } elseif (strpos($liveContent, '音乐') !== false) {
            $probabilities['like'] *= 1.3;
            $probabilities['gift'] *= 1.2;
        } elseif (strpos($liveContent, '教程') !== false) {
            $probabilities['comment'] *= 1.4;
            $probabilities['like'] *= 1.2;
        }

        // 归一化概率
        $total = array_sum($probabilities);
        foreach ($probabilities as $key => $value) {
            $probabilities[$key] = $value / $total;
        }

        return $probabilities;
    }

    protected static function weightedRandom($probabilities)
    {
        $rand = mt_rand() / mt_getrandmax();
        $cumulative = 0;
        
        foreach ($probabilities as $item => $probability) {
            $cumulative += $probability;
            if ($rand <= $cumulative) {
                return $item;
            }
        }
        
        return array_keys($probabilities)[0];
    }

    protected static function getLiveContext($live_id)
    {
        // 这里可以根据直播内容、标题等生成上下文
        $live = self::getLiveDetail($live_id, false);
        if ($live) {
            $title = $live['live_title'];
            // 从标题中提取关键词
            $keywords = self::extractKeywords($title);
            if (!empty($keywords)) {
                return $keywords[array_rand($keywords)];
            }
        }
        return '';
    }

    protected static function extractKeywords($text)
    {
        // 简单的关键词提取
        $keywords = [];
        $keywordPatterns = [
            '/(电影|电视剧|综艺|动漫|游戏|音乐|体育|直播)/i',
            '/(教程|分享|讲解|演示|展示)/i',
            '/(新品|新片|新剧|新游戏)/i'
        ];
        
        foreach ($keywordPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $keywords[] = $matches[1];
            }
        }
        
        return $keywords;
    }

    protected static function addIntelligentComment($live_id, $context = '')
    {
        // 基础评论模板
        $commentTemplates = [
            '这个直播真好看！',
            '主播好厉害',
            '太精彩了',
            '支持支持',
            '这个内容很有价值',
            '主播辛苦了',
            '期待更多内容',
            '非常喜欢这个直播',
            '内容质量很高',
            '主播讲解得很详细',
            '这个直播很有趣',
            '学到了很多',
            '感谢主播的分享',
            '内容很棒',
            '继续加油',
            '已关注主播',
            '这个技巧很实用',
            '讲解很清晰',
            '画面很清晰',
            '声音很好听'
        ];

        // 上下文相关评论
        if ($context) {
            $contextComments = [
                "关于{$context}的内容很精彩",
                "{$context}这个话题讲得很好",
                "学到了关于{$context}的知识",
                "{$context}的内容很有价值",
                "{$context}部分讲解得很详细",
                "对{$context}的分析很到位",
                "关于{$context}的技巧很实用",
                "{$context}的内容让我受益匪浅"
            ];
            $comment = $contextComments[array_rand($contextComments)];
        } else {
            $comment = $commentTemplates[array_rand($commentTemplates)];
        }

        // 生成真实的用户名
        $username = self::getRealisticUsername();

        // 保存评论到缓存
        $cacheKey = self::$cachePrefix . 'comments_' . $live_id;
        $comments = Cache::get($cacheKey, []);
        
        $comments[] = [
            'username' => $username,
            'content' => $comment,
            'time' => time()
        ];

        // 保持评论数量在合理范围内
        if (count($comments) > 50) {
            array_shift($comments);
        }

        Cache::set($cacheKey, $comments, 3600);
    }

    protected static function getIntelligentDanmaku($duration, $viewerCount, $liveContent = '')
    {
        $danmakuContents = [
            '666', '好看', '主播好', '太棒了', '继续加油', '支持你',
            '学到了', '感谢分享', '很有价值', '内容很棒', '主播辛苦了',
            '期待更多', '非常喜欢', '内容质量高', '讲解详细', '很有帮助',
            '厉害了', '太强了', '佩服佩服', '666666', '牛啊牛啊',
            '学到很多', '干货满满', '感谢主播', '必须支持', '已关注'
        ];

        // 根据直播内容生成特定弹幕
        if (strpos($liveContent, '游戏') !== false) {
            $gameDanmakus = ['操作太秀了', '66666', '这波操作无敌', '厉害厉害', '玩得真好', '这个技巧学到了'];
            if (mt_rand(0, 1)) {
                return $gameDanmakus[array_rand($gameDanmakus)];
            }
        } elseif (strpos($liveContent, '音乐') !== false) {
            $musicDanmakus = ['歌声真好听', '太有才华了', '耳朵怀孕了', '唱功一流', '音乐真棒', '再来一首'];
            if (mt_rand(0, 1)) {
                return $musicDanmakus[array_rand($musicDanmakus)];
            }
        } elseif (strpos($liveContent, '教程') !== false) {
            $tutorialDanmakus = ['讲得真清楚', '学到了', '太详细了', '这个方法好', '感谢分享', '实用干货'];
            if (mt_rand(0, 1)) {
                return $tutorialDanmakus[array_rand($tutorialDanmakus)];
            }
        }

        // 根据直播时长选择不同的弹幕
        if ($duration < 300) {
            $startDanmakus = ['刚来，主播好', '开始了吗', '终于等到了', '期待已久', '来了来了', '开播大吉'];
            if (mt_rand(0, 1)) {
                return $startDanmakus[array_rand($startDanmakus)];
            }
        } elseif ($duration > 1800) {
            $lateDanmakus = ['主播辛苦了', '还没结束吧', '精彩继续', '意犹未尽', '下次什么时候播', '舍不得结束'];
            if (mt_rand(0, 2)) {
                return $lateDanmakus[array_rand($lateDanmakus)];
            }
        }

        // 根据观众数量生成不同弹幕
        if ($viewerCount > 100) {
            $popularDanmakus = ['人气好高', '好多人啊', '这么多人', '火了火了', '主播要火', '热闹热闹'];
            if (mt_rand(0, 2)) {
                return $popularDanmakus[array_rand($popularDanmakus)];
            }
        }

        return $danmakuContents[array_rand($danmakuContents)];
    }

    protected static function getAppropriateGift($duration, $viewerCount, $liveContent = '')
    {
        // 根据直播内容选择礼物类型
        if (strpos($liveContent, '音乐') !== false) {
            // 音乐直播适合送爱心、鲜花等表达欣赏的礼物
            $musicGifts = array_filter(self::$virtualGifts, function($gift) {
                return in_array($gift['id'], [1, 2, 3, 5]); // 鲜花、掌声、爱心、钻石
            });
            if (!empty($musicGifts)) {
                return $musicGifts[array_rand($musicGifts)];
            }
        } elseif (strpos($liveContent, '游戏') !== false) {
            // 游戏直播适合送火箭、超级火箭等激动人心的礼物
            $gameGifts = array_filter(self::$virtualGifts, function($gift) {
                return in_array($gift['id'], [4, 8]); // 火箭、超级火箭
            });
            if (!empty($gameGifts)) {
                return $gameGifts[array_rand($gameGifts)];
            }
        } elseif (strpos($liveContent, '教程') !== false) {
            // 教程直播适合送爱心、钻石等表达感谢的礼物
            $tutorialGifts = array_filter(self::$virtualGifts, function($gift) {
                return in_array($gift['id'], [3, 5]); // 爱心、钻石
            });
            if (!empty($tutorialGifts)) {
                return $tutorialGifts[array_rand($tutorialGifts)];
            }
        }

        // 根据直播时长和观众数量选择合适的礼物
        if ($duration > 1200 && $viewerCount > 50) {
            // 直播时间较长且观众较多，选择价值较高的礼物
            $highValueGifts = array_filter(self::$virtualGifts, function($gift) {
                return $gift['price'] >= 10;
            });
            if (!empty($highValueGifts)) {
                return $highValueGifts[array_rand($highValueGifts)];
            }
        } elseif ($duration < 600 || $viewerCount < 30) {
            // 直播刚开始或观众较少，选择价值较低的礼物
            $lowValueGifts = array_filter(self::$virtualGifts, function($gift) {
                return $gift['price'] <= 5;
            });
            if (!empty($lowValueGifts)) {
                return $lowValueGifts[array_rand($lowValueGifts)];
            }
        }
        
        // 一般情况随机选择礼物
        return self::$virtualGifts[array_rand(self::$virtualGifts)];
    }

    protected static function getRealisticUsername()
    {
        $prefixes = ['热心', '积极', '快乐', '阳光', '可爱', '聪明', '善良', '勇敢', '幽默', '认真'];
        $suffixes = ['观众', '粉丝', '用户', '朋友', '同学', '网友', '看官', '支持者', '爱好者', '学习者'];
        $numbers = mt_rand(100, 999);
        
        return $prefixes[array_rand($prefixes)] . $suffixes[array_rand($suffixes)] . $numbers;
    }

    public static function startAutoInteractionTask($live_id, $interval = 30)
    {
        $cacheKey = self::$cachePrefix . 'auto_task_' . $live_id;
        
        if (Cache::get($cacheKey)) {
            return ['code' => 0, 'msg' => '自动互动任务已在运行'];
        }

        Cache::set($cacheKey, 1, 86400);
        
        $taskKey = self::$cachePrefix . 'task_' . $live_id;
        Cache::set($taskKey, [
            'live_id' => $live_id,
            'start_time' => time(),
            'interval' => $interval,
            'last_run' => 0
        ], 86400);

        return ['code' => 1, 'msg' => '自动互动任务已启动'];
    }

    public static function stopAutoInteractionTask($live_id)
    {
        $cacheKey = self::$cachePrefix . 'auto_task_' . $live_id;
        $taskKey = self::$cachePrefix . 'task_' . $live_id;
        
        Cache::clear($cacheKey);
        Cache::clear($taskKey);

        return ['code' => 1, 'msg' => '自动互动任务已停止'];
    }

    public static function getAutoInteractionTasks()
    {
        $tasks = [];
        $pattern = self::$cachePrefix . 'task_*';
        
        return ['code' => 1, 'data' => $tasks];
    }

    public static function addTimedAnnouncement($live_id, $announcement, $delay = 60)
    {
        $cacheKey = self::$cachePrefix . 'announcements_' . $live_id;
        $announcements = Cache::get($cacheKey, []);
        
        $announcementData = [
            'content' => $announcement,
            'send_time' => time() + $delay,
            'id' => md5(uniqid())
        ];
        
        array_push($announcements, $announcementData);
        Cache::set($cacheKey, $announcements, 86400);

        return ['code' => 1, 'msg' => '定时公告已添加', 'data' => $announcementData];
    }

    public static function getAnnouncements($live_id)
    {
        $cacheKey = self::$cachePrefix . 'announcements_' . $live_id;
        $announcements = Cache::get($cacheKey, []);
        
        $now = time();
        $dueAnnouncements = [];
        $remainingAnnouncements = [];

        foreach ($announcements as $announcement) {
            if ($announcement['send_time'] <= $now) {
                $dueAnnouncements[] = $announcement;
            } else {
                $remainingAnnouncements[] = $announcement;
            }
        }

        Cache::set($cacheKey, $remainingAnnouncements, 86400);

        return ['code' => 1, 'data' => $dueAnnouncements];
    }

    public static function getViewerDiversity($live_id)
    {
        $viewerTypes = [
            'silent_viewer' => ['name' => '沉默观众', 'percentage' => 40],
            'active_commenter' => ['name' => '活跃评论者', 'percentage' => 25],
            'gift_sender' => ['name' => '送礼达人', 'percentage' => 15],
            'liker' => ['name' => '点赞狂魔', 'percentage' => 20]
        ];

        $viewerCount = self::getLiveViewerCount($live_id);
        $diversity = [];

        foreach ($viewerTypes as $key => $type) {
            $count = floor($viewerCount * $type['percentage'] / 100);
            $diversity[$key] = [
                'name' => $type['name'],
                'percentage' => $type['percentage'],
                'count' => $count
            ];
        }

        return ['code' => 1, 'data' => $diversity];
    }

    protected static function getLiveViewerCount($live_id)
    {
        $live = self::getLiveDetail($live_id, false);
        return $live ? $live['live_viewers'] : 0;
    }

    public static function getGiftRanking($live_id, $limit = 10)
    {
        $cacheKey = self::$cachePrefix . 'gifts_' . $live_id;
        $gifts = Cache::get($cacheKey, []);

        $ranking = [];
        foreach ($gifts as $gift) {
            $username = $gift['username'];
            $amount = $gift['gift_price'] * $gift['count'];

            if (!isset($ranking[$username])) {
                $ranking[$username] = ['username' => $username, 'total' => 0, 'gifts' => []];
            }

            $ranking[$username]['total'] += $amount;
            $ranking[$username]['gifts'][] = $gift;
        }

        usort($ranking, function($a, $b) {
            return $b['total'] - $a['total'];
        });

        return array_slice($ranking, 0, $limit);
    }

    public static function generateCustomComment($live_id, $context = '')
    {
        $commentTemplates = [
            '这个内容很有意思',
            '学到了很多东西',
            '感谢主播的分享',
            '内容质量很高',
            '必须支持一下',
            '主播讲得很详细',
            '期待更多内容',
            '干货满满',
            '666666',
            '太厉害了'
        ];

        $comment = $commentTemplates[array_rand($commentTemplates)];
        
        if ($context) {
            $contextComments = [
                "关于{$context}的内容很精彩",
                "{$context}这个话题讲得很好",
                "学到了关于{$context}的知识",
                "{$context}的内容很有价值"
            ];
            if (mt_rand(0, 1)) {
                $comment = $contextComments[array_rand($contextComments)];
            }
        }

        return $comment;
    }

    protected static $userLevels = [
        ['level' => 1, 'name' => '新手', 'min_points' => 0, 'icon' => '⭐'],
        ['level' => 2, 'name' => '学徒', 'min_points' => 100, 'icon' => '🌟'],
        ['level' => 3, 'name' => '粉丝', 'min_points' => 500, 'icon' => '✨'],
        ['level' => 4, 'name' => '铁粉', 'min_points' => 1000, 'icon' => '💫'],
        ['level' => 5, 'name' => '钻石粉', 'min_points' => 5000, 'icon' => '💎'],
        ['level' => 6, 'name' => '至尊粉', 'min_points' => 10000, 'icon' => '👑']
    ];

    public static function getUserLevel($points)
    {
        for ($i = count(self::$userLevels) - 1; $i >= 0; $i--) {
            if ($points >= self::$userLevels[$i]['min_points']) {
                return self::$userLevels[$i];
            }
        }
        return self::$userLevels[0];
    }

    public static function updateUserPoints($live_id, $user_id, $points)
    {
        $cacheKey = self::$cachePrefix . 'user_points_' . $live_id . '_' . $user_id;
        $currentPoints = Cache::get($cacheKey, 0);
        $newPoints = $currentPoints + $points;
        
        Cache::set($cacheKey, $newPoints, 86400);
        
        $oldLevel = self::getUserLevel($currentPoints);
        $newLevel = self::getUserLevel($newPoints);
        
        $levelUp = $newLevel['level'] > $oldLevel['level'];
        
        return [
            'code' => 1,
            'points' => $newPoints,
            'level' => $newLevel,
            'level_up' => $levelUp
        ];
    }

    public static function getLotteryPrizes()
    {
        return [
            ['id' => 1, 'name' => '再来一次', 'probability' => 0.2, 'type' => 'bonus'],
            ['id' => 2, 'name' => '鲜花x10', 'probability' => 0.25, 'type' => 'gift', 'gift_id' => 1, 'count' => 10],
            ['id' => 3, 'name' => '爱心x5', 'probability' => 0.2, 'type' => 'gift', 'gift_id' => 3, 'count' => 5],
            ['id' => 4, 'name' => '火箭x1', 'probability' => 0.15, 'type' => 'gift', 'gift_id' => 4, 'count' => 1],
            ['id' => 5, 'name' => '钻石x2', 'probability' => 0.1, 'type' => 'gift', 'gift_id' => 5, 'count' => 2],
            ['id' => 6, 'name' => '谢谢参与', 'probability' => 0.1, 'type' => 'none']
        ];
    }

    public static function drawLottery($live_id, $user_id)
    {
        $prizes = self::getLotteryPrizes();
        $random = mt_rand(1, 1000) / 1000;
        $cumulative = 0;
        
        foreach ($prizes as $prize) {
            $cumulative += $prize['probability'];
            if ($random <= $cumulative) {
                if ($prize['type'] == 'gift') {
                    self::sendVirtualGift($live_id, $prize['gift_id'], $user_id, $prize['count']);
                }
                return ['code' => 1, 'prize' => $prize];
            }
        }
        
        return ['code' => 1, 'prize' => $prizes[5]];
    }

    protected static $pkStatus = [];

    public static function startPk($live_id_1, $live_id_2, $duration = 300)
    {
        $pkId = md5($live_id_1 . '_' . $live_id_2 . '_' . time());
        
        self::$pkStatus[$pkId] = [
            'id' => $pkId,
            'live_id_1' => $live_id_1,
            'live_id_2' => $live_id_2,
            'score_1' => 0,
            'score_2' => 0,
            'start_time' => time(),
            'end_time' => time() + $duration,
            'status' => 'active'
        ];
        
        $cacheKey = self::$cachePrefix . 'pk_' . $pkId;
        Cache::set($cacheKey, self::$pkStatus[$pkId], $duration + 60);
        
        return ['code' => 1, 'data' => self::$pkStatus[$pkId]];
    }

    public static function getPkStatus($pk_id)
    {
        $cacheKey = self::$cachePrefix . 'pk_' . $pk_id;
        $pk = Cache::get($cacheKey);
        
        if (!$pk) {
            return ['code' => 0, 'msg' => 'PK不存在'];
        }
        
        if (time() >= $pk['end_time'] && $pk['status'] == 'active') {
            $pk['status'] = 'ended';
            $pk['winner'] = $pk['score_1'] > $pk['score_2'] ? 1 : ($pk['score_2'] > $pk['score_1'] ? 2 : 0);
            Cache::set($cacheKey, $pk, 3600);
        }
        
        return ['code' => 1, 'data' => $pk];
    }

    public static function updatePkScore($pk_id, $live_side, $score)
    {
        $cacheKey = self::$cachePrefix . 'pk_' . $pk_id;
        $pk = Cache::get($cacheKey);
        
        if (!$pk || $pk['status'] != 'active') {
            return ['code' => 0, 'msg' => 'PK不存在或已结束'];
        }
        
        if ($live_side == 1) {
            $pk['score_1'] += $score;
        } else {
            $pk['score_2'] += $score;
        }
        
        Cache::set($cacheKey, $pk, $pk['end_time'] - time() + 60);
        
        return ['code' => 1, 'data' => $pk];
    }

    public static function sendComboGift($live_id, $gift_id, $user_id, $count, $combo_count)
    {
        $gift = self::getVirtualGiftById($gift_id);
        if (!$gift) {
            return ['code' => 0, 'msg' => '礼物不存在'];
        }
        
        $totalCount = $count * $combo_count;
        $result = self::sendVirtualGift($live_id, $gift_id, $user_id, $totalCount);
        
        if ($result['code'] == 1) {
            $result['data']['combo_count'] = $combo_count;
            $result['data']['is_combo'] = true;
        }
        
        return $result;
    }

    public static function getSuperGifts()
    {
        $superGifts = array_filter(self::$virtualGifts, function($gift) {
            return $gift['price'] >= 50;
        });
        
        return ['code' => 1, 'data' => array_values($superGifts)];
    }
}