<?php
namespace app\common\util;

use think\Cache;
use think\Db;
use app\common\model\Art as ArtModel;

class ArtService
{
    protected static $cachePrefix = 'art_service_';
    protected static $cacheTime = 3600;

    public static function getArtDetail($art_id, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'detail_' . $art_id;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $art = ArtModel::get($art_id);
        if ($art && $use_cache) {
            Cache::set($cacheKey, $art, self::$cacheTime);
        }

        return $art;
    }

    public static function getArtList($where = [], $page = 1, $limit = 20, $order = 'art_time desc', $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'list_' . md5(json_encode($where) . '_' . $page . '_' . $limit . '_' . $order);
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $query = ArtModel::where('art_status', 1);
        
        if (!empty($where)) {
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    $query->where($key, $value[0], $value[1]);
                } else {
                    $query->where($key, $value);
                }
            }
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
            Cache::set($cacheKey, $result, 1800);
        }

        return $result;
    }

    public static function updateArtHits($art_id, $type = 'hits')
    {
        Db::startTrans();
        try {
            $art = ArtModel::get($art_id);
            if (!$art) {
                Db::rollback();
                return false;
            }

            $updateData = [];
            switch ($type) {
                case 'hits':
                    $updateData['art_hits'] = Db::raw('art_hits + 1');
                    break;
                case 'up':
                    $updateData['art_up'] = Db::raw('art_up + 1');
                    break;
                case 'down':
                    $updateData['art_down'] = Db::raw('art_down + 1');
                    break;
            }

            ArtModel::update($updateData, ['art_id' => $art_id]);
            self::clearArtCache($art_id);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    public static function getLatestArts($type_id = 0, $limit = 10, $use_cache = true)
    {
        return self::getArtList(
            $type_id > 0 ? ['type_id' => $type_id] : [],
            1,
            $limit,
            'art_time desc',
            $use_cache
        );
    }

    public static function getHotArts($type_id = 0, $limit = 10, $use_cache = true)
    {
        return self::getArtList(
            $type_id > 0 ? ['type_id' => $type_id] : [],
            1,
            $limit,
            'art_hits desc',
            $use_cache
        );
    }

    public static function getRandomArts($count = 10, $type_id = 0, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'random_' . $count . '_' . $type_id;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $query = ArtModel::where('art_status', 1);
        if ($type_id > 0) {
            $query->where('type_id', $type_id);
        }

        $result = $query->orderRaw('RAND()')->limit($count)->select();

        if ($use_cache && !empty($result)) {
            Cache::set($cacheKey, $result, 600);
        }

        return $result;
    }

    protected static function clearArtCache($art_id)
    {
        $patterns = [
            self::$cachePrefix . 'detail_' . $art_id
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

    public static function getArtStatistics($type_id = 0, $start_time = 0, $end_time = 0)
    {
        $query = ArtModel::where('art_status', 1);

        if ($type_id > 0) {
            $query->where('type_id', $type_id);
        }

        if ($start_time > 0) {
            $query->where('art_time', '>=', $start_time);
        }

        if ($end_time > 0) {
            $query->where('art_time', '<=', $end_time);
        }

        $totalArts = $query->count();
        $totalHits = $query->sum('art_hits');
        $totalUp = $query->sum('art_up');
        $totalDown = $query->sum('art_down');

        return [
            'code' => 1,
            'data' => [
                'total_arts' => $totalArts,
                'total_hits' => $totalHits,
                'total_up' => $totalUp,
                'total_down' => $totalDown
            ]
        ];
    }
}
