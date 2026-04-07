<?php
namespace app\common\util;

use think\Cache;
use think\Db;
use app\common\model\UserBehavior as UserBehaviorModel;

class UserBehaviorService
{
    protected static $cachePrefix = 'user_behavior_';
    protected static $cacheTime = 3600;

    public static function addPlayHistory($user_id, $vod_id, $sid = 0, $nid = 0, $play_time = 0, $progress = 0, $extra = [])
    {
        Db::startTrans();
        try {
            $behavior = UserBehaviorModel::where([
                'user_id' => $user_id,
                'behavior_type' => 'play',
                'vod_id' => $vod_id
            ])->find();

            $data = [
                'user_id' => $user_id,
                'behavior_type' => 'play',
                'vod_id' => $vod_id,
                'sid' => $sid,
                'nid' => $nid,
                'play_time' => $play_time,
                'progress' => $progress,
                'behavior_time' => time(),
                'behavior_extra' => !empty($extra) ? json_encode($extra) : ''
            ];

            $result = null;
            if ($behavior) {
                $result = UserBehaviorModel::update($data, ['behavior_id' => $behavior['behavior_id']]);
            } else {
                $result = UserBehaviorModel::create($data);
            }

            Db::commit();
            
            self::clearUserCache($user_id);
            self::updateVodPlayCount($vod_id);
            
            return $result;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    public static function toggleFavorite($user_id, $vod_id, $extra = [])
    {
        Db::startTrans();
        try {
            $behavior = UserBehaviorModel::where([
                'user_id' => $user_id,
                'behavior_type' => 'favorite',
                'vod_id' => $vod_id
            ])->find();

            $result = null;
            $isFavorited = false;

            if ($behavior) {
                $result = UserBehaviorModel::destroy($behavior['behavior_id']);
                $isFavorited = false;
                self::updateVodFavoriteCount($vod_id, -1);
            } else {
                $data = [
                    'user_id' => $user_id,
                    'behavior_type' => 'favorite',
                    'vod_id' => $vod_id,
                    'behavior_time' => time(),
                    'behavior_extra' => !empty($extra) ? json_encode($extra) : ''
                ];
                $result = UserBehaviorModel::create($data);
                $isFavorited = true;
                self::updateVodFavoriteCount($vod_id, 1);
            }

            Db::commit();
            self::clearUserCache($user_id);
            
            return ['code' => 1, 'is_favorited' => $isFavorited, 'result' => $result];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    public static function toggleFollow($user_id, $vod_id, $extra = [])
    {
        Db::startTrans();
        try {
            $behavior = UserBehaviorModel::where([
                'user_id' => $user_id,
                'behavior_type' => 'follow',
                'vod_id' => $vod_id
            ])->find();

            $result = null;
            $isFollowed = false;

            if ($behavior) {
                $result = UserBehaviorModel::destroy($behavior['behavior_id']);
                $isFollowed = false;
            } else {
                $data = [
                    'user_id' => $user_id,
                    'behavior_type' => 'follow',
                    'vod_id' => $vod_id,
                    'behavior_time' => time(),
                    'behavior_extra' => !empty($extra) ? json_encode($extra) : ''
                ];
                $result = UserBehaviorModel::create($data);
                $isFollowed = true;
            }

            Db::commit();
            self::clearUserCache($user_id);
            
            return ['code' => 1, 'is_followed' => $isFollowed, 'result' => $result];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    public static function toggleLike($user_id, $vod_id = 0, $art_id = 0, $extra = [])
    {
        Db::startTrans();
        try {
            $where = [
                'user_id' => $user_id,
                'behavior_type' => 'like'
            ];
            if ($vod_id > 0) {
                $where['vod_id'] = $vod_id;
            }
            if ($art_id > 0) {
                $where['art_id'] = $art_id;
            }

            $behavior = UserBehaviorModel::where($where)->find();

            $result = null;
            $isLiked = false;

            if ($behavior) {
                $result = UserBehaviorModel::destroy($behavior['behavior_id']);
                $isLiked = false;
                if ($vod_id > 0) {
                    self::updateVodLikeCount($vod_id, -1);
                }
            } else {
                $data = [
                    'user_id' => $user_id,
                    'behavior_type' => 'like',
                    'vod_id' => $vod_id,
                    'art_id' => $art_id,
                    'behavior_time' => time(),
                    'behavior_extra' => !empty($extra) ? json_encode($extra) : ''
                ];
                $result = UserBehaviorModel::create($data);
                $isLiked = true;
                if ($vod_id > 0) {
                    self::updateVodLikeCount($vod_id, 1);
                }
            }

            Db::commit();
            self::clearUserCache($user_id);
            
            return ['code' => 1, 'is_liked' => $isLiked, 'result' => $result];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    public static function getPlayHistory($user_id, $page = 1, $limit = 20, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'play_' . $user_id . '_' . $page . '_' . $limit;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $result = UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'play'
        ])->order('behavior_time desc')->page($page, $limit)->select();

        if ($use_cache && !empty($result)) {
            Cache::set($cacheKey, $result, self::$cacheTime);
        }

        return $result;
    }

    public static function getFavorites($user_id, $page = 1, $limit = 20, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'fav_' . $user_id . '_' . $page . '_' . $limit;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $result = UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'favorite'
        ])->order('behavior_time desc')->page($page, $limit)->select();

        if ($use_cache && !empty($result)) {
            Cache::set($cacheKey, $result, self::$cacheTime);
        }

        return $result;
    }

    public static function getFollows($user_id, $page = 1, $limit = 20, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'follow_' . $user_id . '_' . $page . '_' . $limit;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $result = UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'follow'
        ])->order('behavior_time desc')->page($page, $limit)->select();

        if ($use_cache && !empty($result)) {
            Cache::set($cacheKey, $result, self::$cacheTime);
        }

        return $result;
    }

    public static function getUserBehaviorCount($user_id, $behavior_type, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'count_' . $user_id . '_' . $behavior_type;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }

        $count = UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => $behavior_type
        ])->count();

        if ($use_cache) {
            Cache::set($cacheKey, $count, self::$cacheTime);
        }

        return $count;
    }

    public static function isFavorited($user_id, $vod_id, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'isfav_' . $user_id . '_' . $vod_id;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $result = UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'favorite',
            'vod_id' => $vod_id
        ])->count() > 0;

        if ($use_cache) {
            Cache::set($cacheKey, $result, 600);
        }

        return $result;
    }

    public static function isFollowed($user_id, $vod_id, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'isfollow_' . $user_id . '_' . $vod_id;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $result = UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'follow',
            'vod_id' => $vod_id
        ])->count() > 0;

        if ($use_cache) {
            Cache::set($cacheKey, $result, 600);
        }

        return $result;
    }

    public static function isLiked($user_id, $vod_id = 0, $art_id = 0, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'islike_' . $user_id . '_' . $vod_id . '_' . $art_id;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $where = [
            'user_id' => $user_id,
            'behavior_type' => 'like'
        ];
        if ($vod_id > 0) {
            $where['vod_id'] = $vod_id;
        }
        if ($art_id > 0) {
            $where['art_id'] = $art_id;
        }

        $result = UserBehaviorModel::where($where)->count() > 0;

        if ($use_cache) {
            Cache::set($cacheKey, $result, 600);
        }

        return $result;
    }

    public static function getPlayProgress($user_id, $vod_id)
    {
        $behavior = UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'play',
            'vod_id' => $vod_id
        ])->find();

        if ($behavior) {
            return [
                'sid' => $behavior['sid'],
                'nid' => $behavior['nid'],
                'play_time' => $behavior['play_time'],
                'progress' => $behavior['progress'] ?? 0,
                'time' => $behavior['behavior_time']
            ];
        }

        return null;
    }

    public static function clearOldHistory($user_id, $days = 90)
    {
        $time = time() - ($days * 86400);
        $result = UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'play',
            'behavior_time' => ['lt', $time]
        ])->delete();
        
        self::clearUserCache($user_id);
        
        return $result;
    }

    protected static function updateVodPlayCount($vod_id)
    {
        try {
            $vodModel = \app\common\model\Vod::get($vod_id);
            if ($vodModel) {
                $vodModel->vod_hits += 1;
                $vodModel->vod_hits_day += 1;
                $vodModel->vod_hits_week += 1;
                $vodModel->vod_hits_month += 1;
                $vodModel->save();
            }
        } catch (\Exception $e) {
        }
    }

    protected static function updateVodFavoriteCount($vod_id, $delta)
    {
        try {
            $vodModel = \app\common\model\Vod::get($vod_id);
            if ($vodModel) {
                $vodModel->vod_up += $delta;
                $vodModel->save();
            }
        } catch (\Exception $e) {
        }
    }

    protected static function updateVodLikeCount($vod_id, $delta)
    {
        try {
            $vodModel = \app\common\model\Vod::get($vod_id);
            if ($vodModel) {
                $vodModel->vod_up += $delta;
                $vodModel->save();
            }
        } catch (\Exception $e) {
        }
    }

    protected static function clearUserCache($user_id)
    {
        $patterns = [
            self::$cachePrefix . 'play_' . $user_id . '_*',
            self::$cachePrefix . 'fav_' . $user_id . '_*',
            self::$cachePrefix . 'follow_' . $user_id . '_*',
            self::$cachePrefix . 'count_' . $user_id . '_*',
            self::$cachePrefix . 'isfav_' . $user_id . '_*',
            self::$cachePrefix . 'isfollow_' . $user_id . '_*',
            self::$cachePrefix . 'islike_' . $user_id . '_*'
        ];

        foreach ($patterns as $pattern) {
            Cache::clear($pattern);
        }
    }

    public static function clearCache($user_id = null)
    {
        if ($user_id) {
            self::clearUserCache($user_id);
        } else {
            Cache::clear(self::$cachePrefix . '*');
        }
        return ['code' => 1, 'msg' => '缓存清除成功'];
    }

    public static function updateWatchDuration($user_id, $vod_id, $duration)
    {
        Db::startTrans();
        try {
            $behavior = UserBehaviorModel::where([
                'user_id' => $user_id,
                'behavior_type' => 'play',
                'vod_id' => $vod_id
            ])->find();

            if ($behavior) {
                $extra = json_decode($behavior['behavior_extra'], true);
                $extra = is_array($extra) ? $extra : [];
                $extra['total_duration'] = isset($extra['total_duration']) ? $extra['total_duration'] + $duration : $duration;
                $extra['watch_count'] = isset($extra['watch_count']) ? $extra['watch_count'] + 1 : 1;
                
                UserBehaviorModel::update([
                    'behavior_extra' => json_encode($extra),
                    'behavior_time' => time()
                ], ['behavior_id' => $behavior['behavior_id']]);
            }

            Db::commit();
            self::clearUserCache($user_id);
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    public static function getWatchStatistics($user_id, $days = 30)
    {
        $startTime = time() - ($days * 86400);
        
        $behaviors = UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'play'
        ])->where('behavior_time', '>=', $startTime)->select();

        $totalDuration = 0;
        $totalVods = [];
        $typeCounts = [];

        foreach ($behaviors as $behavior) {
            $extra = json_decode($behavior['behavior_extra'], true);
            $extra = is_array($extra) ? $extra : [];
            $totalDuration += isset($extra['total_duration']) ? $extra['total_duration'] : 0;
            $totalVods[$behavior['vod_id']] = true;

            if ($behavior['vod_id'] > 0) {
                $vod = \app\common\model\Vod::get($behavior['vod_id']);
                if ($vod && $vod['type_id']) {
                    $typeId = $vod['type_id'];
                    if (!isset($typeCounts[$typeId])) {
                        $typeCounts[$typeId] = 0;
                    }
                    $typeCounts[$typeId]++;
                }
            }
        }

        return [
            'code' => 1,
            'data' => [
                'total_duration' => $totalDuration,
                'total_vods' => count($totalVods),
                'type_counts' => $typeCounts,
                'days' => $days
            ]
        ];
    }

    public static function batchClearHistory($user_id, $vod_ids = [])
    {
        Db::startTrans();
        try {
            $where = [
                'user_id' => $user_id,
                'behavior_type' => 'play'
            ];

            if (!empty($vod_ids)) {
                $where['vod_id'] = ['in', $vod_ids];
            }

            UserBehaviorModel::where($where)->delete();

            Db::commit();
            self::clearUserCache($user_id);
            return ['code' => 1, 'msg' => '清空成功'];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '清空失败: ' . $e->getMessage()];
        }
    }

    public static function batchRemoveFavorites($user_id, $vod_ids = [])
    {
        Db::startTrans();
        try {
            $where = [
                'user_id' => $user_id,
                'behavior_type' => 'favorite'
            ];

            if (!empty($vod_ids)) {
                $where['vod_id'] = ['in', $vod_ids];
            }

            $behaviors = UserBehaviorModel::where($where)->select();
            foreach ($behaviors as $behavior) {
                self::updateVodFavoriteCount($behavior['vod_id'], -1);
                UserBehaviorModel::destroy($behavior['behavior_id']);
            }

            Db::commit();
            self::clearUserCache($user_id);
            return ['code' => 1, 'msg' => '取消收藏成功'];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '取消收藏失败: ' . $e->getMessage()];
        }
    }

    public static function getRecommendVods($user_id, $limit = 10)
    {
        $cacheKey = self::$cachePrefix . 'recommend_' . $user_id . '_' . $limit;
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $favorites = self::getFavorites($user_id, 1, 100, false);
        $history = self::getPlayHistory($user_id, 1, 100, false);

        $typeIds = [];
        $vodIds = [];

        foreach ($favorites as $fav) {
            if ($fav['vod_id'] > 0) {
                $vodIds[] = $fav['vod_id'];
                $vod = \app\common\model\Vod::get($fav['vod_id']);
                if ($vod && $vod['type_id']) {
                    $typeIds[$vod['type_id']] = true;
                }
            }
        }

        foreach ($history as $hist) {
            if ($hist['vod_id'] > 0) {
                $vodIds[] = $hist['vod_id'];
                $vod = \app\common\model\Vod::get($hist['vod_id']);
                if ($vod && $vod['type_id']) {
                    $typeIds[$vod['type_id']] = true;
                }
            }
        }

        $vodIds = array_unique($vodIds);
        $typeIds = array_keys($typeIds);

        $query = \app\common\model\Vod::where('vod_status', 1);

        if (!empty($typeIds)) {
            $query->where('type_id', 'in', $typeIds);
        }

        if (!empty($vodIds)) {
            $query->where('vod_id', 'not in', $vodIds);
        }

        $result = $query->order('vod_hits desc, vod_time desc')->limit($limit)->select();

        Cache::set($cacheKey, $result, 1800);

        return $result;
    }

    public static function getSimilarVods($vod_id, $limit = 10)
    {
        $cacheKey = self::$cachePrefix . 'similar_' . $vod_id . '_' . $limit;
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $vod = \app\common\model\Vod::get($vod_id);
        if (!$vod) {
            return [];
        }

        $query = \app\common\model\Vod::where('vod_status', 1)
            ->where('vod_id', 'neq', $vod_id);

        if ($vod['type_id']) {
            $query->where('type_id', $vod['type_id']);
        }

        if ($vod['vod_area']) {
            $query->whereOr('vod_area', 'like', '%' . $vod['vod_area'] . '%');
        }

        if ($vod['vod_year']) {
            $query->whereOr('vod_year', $vod['vod_year']);
        }

        $result = $query->order('vod_hits desc')->limit($limit)->select();

        Cache::set($cacheKey, $result, 3600);

        return $result;
    }

    public static function getHotVods($type_id = 0, $limit = 10, $period = 'day')
    {
        $cacheKey = self::$cachePrefix . 'hot_' . $type_id . '_' . $limit . '_' . $period;
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $query = \app\common\model\Vod::where('vod_status', 1);

        if ($type_id > 0) {
            $query->where('type_id', $type_id);
        }

        switch ($period) {
            case 'day':
                $query->order('vod_hits_day desc');
                break;
            case 'week':
                $query->order('vod_hits_week desc');
                break;
            case 'month':
                $query->order('vod_hits_month desc');
                break;
            default:
                $query->order('vod_hits desc');
        }

        $result = $query->order('vod_time desc')->limit($limit)->select();

        Cache::set($cacheKey, $result, 1800);

        return $result;
    }

    public static function addComment($user_id, $vod_id = 0, $art_id = 0, $content = '', $extra = [])
    {
        Db::startTrans();
        try {
            $data = [
                'user_id' => $user_id,
                'behavior_type' => 'comment',
                'vod_id' => $vod_id,
                'art_id' => $art_id,
                'behavior_time' => time(),
                'behavior_extra' => json_encode(array_merge(['content' => $content], $extra))
            ];

            $result = UserBehaviorModel::create($data);

            Db::commit();
            self::clearUserCache($user_id);
            return ['code' => 1, 'result' => $result];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    public static function getComments($vod_id = 0, $art_id = 0, $page = 1, $limit = 20)
    {
        $where = ['behavior_type' => 'comment'];
        if ($vod_id > 0) {
            $where['vod_id'] = $vod_id;
        }
        if ($art_id > 0) {
            $where['art_id'] = $art_id;
        }

        return UserBehaviorModel::where($where)
            ->order('behavior_time desc')
            ->page($page, $limit)
            ->select();
    }
}
