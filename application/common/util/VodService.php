<?php
namespace app\common\util;

use think\Cache;
use think\Db;
use app\common\model\Vod as VodModel;

class VodService
{
    protected static $cachePrefix = 'vod_service_';
    protected static $cacheTime = 3600;

    public static function getVodDetail($vod_id, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'detail_' . $vod_id;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $vod = VodModel::get($vod_id);
        if ($vod && $use_cache) {
            Cache::set($cacheKey, $vod, self::$cacheTime);
        }

        return $vod;
    }

    public static function getVodList($where = [], $page = 1, $limit = 20, $order = 'vod_time desc', $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'list_' . md5(json_encode($where) . '_' . $page . '_' . $limit . '_' . $order);
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $query = VodModel::where('vod_status', 1);
        
        if (!empty($where)) {
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    $query->where($key, $value[0], $value[1]);
                } else {
                    $query->where($key, $value);
                }
            }
        }

        $result = $query->order($order)->page($page, $limit)->select();

        if ($use_cache && !empty($result)) {
            Cache::set($cacheKey, $result, 1800);
        }

        return $result;
    }

    public static function updateVodHits($vod_id, $type = 'hits')
    {
        Db::startTrans();
        try {
            $vod = VodModel::get($vod_id);
            if (!$vod) {
                Db::rollback();
                return false;
            }

            $updateData = [];
            switch ($type) {
                case 'hits':
                    $updateData['vod_hits'] = Db::raw('vod_hits + 1');
                    $updateData['vod_hits_day'] = Db::raw('vod_hits_day + 1');
                    $updateData['vod_hits_week'] = Db::raw('vod_hits_week + 1');
                    $updateData['vod_hits_month'] = Db::raw('vod_hits_month + 1');
                    break;
                case 'up':
                    $updateData['vod_up'] = Db::raw('vod_up + 1');
                    break;
                case 'down':
                    $updateData['vod_down'] = Db::raw('vod_down + 1');
                    break;
                case 'score':
                    $updateData['vod_score'] = Db::raw('vod_score + 1');
                    $updateData['vod_score_all'] = Db::raw('vod_score_all + 1');
                    break;
            }

            VodModel::update($updateData, ['vod_id' => $vod_id]);
            self::clearVodCache($vod_id);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    public static function batchUpdateHits($vod_ids, $type = 'hits')
    {
        if (empty($vod_ids)) {
            return true;
        }

        Db::startTrans();
        try {
            foreach ($vod_ids as $vod_id) {
                self::updateVodHits($vod_id, $type);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    public static function getRandomVods($count = 10, $type_id = 0, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'random_' . $count . '_' . $type_id;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $query = VodModel::where('vod_status', 1);
        if ($type_id > 0) {
            $query->where('type_id', $type_id);
        }

        $result = $query->orderRaw('RAND()')->limit($count)->select();

        if ($use_cache && !empty($result)) {
            Cache::set($cacheKey, $result, 600);
        }

        return $result;
    }

    public static function getLatestVods($type_id = 0, $limit = 10, $use_cache = true)
    {
        return self::getVodList(
            $type_id > 0 ? ['type_id' => $type_id] : [],
            1,
            $limit,
            'vod_time desc',
            $use_cache
        );
    }

    public static function getRelatedVods($vod_id, $limit = 10, $use_cache = true)
    {
        $vod = self::getVodDetail($vod_id, false);
        if (!$vod) {
            return [];
        }

        $cacheKey = self::$cachePrefix . 'related_' . $vod_id . '_' . $limit;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $query = VodModel::where('vod_status', 1)
            ->where('vod_id', 'neq', $vod_id);

        if ($vod['type_id']) {
            $query->where('type_id', $vod['type_id']);
        }

        if ($vod['vod_actor']) {
            $actors = explode(',', $vod['vod_actor']);
            $actorWhere = [];
            foreach ($actors as $actor) {
                $actor = trim($actor);
                if ($actor) {
                    $actorWhere[] = "vod_actor LIKE '%" . $actor . "%'";
                }
            }
            if (!empty($actorWhere)) {
                $query->whereOr(implode(' OR ', $actorWhere));
            }
        }

        $result = $query->order('vod_hits desc')->limit($limit)->select();

        if ($use_cache && !empty($result)) {
            Cache::set($cacheKey, $result, 3600);
        }

        return $result;
    }

    public static function clearVodCache($vod_id)
    {
        $patterns = [
            self::$cachePrefix . 'detail_' . $vod_id,
            self::$cachePrefix . 'related_' . $vod_id . '_*'
        ];

        foreach ($patterns as $pattern) {
            Cache::clear($pattern);
        }
    }

    public static function clearCache($type = null)
    {
        if ($type) {
            Cache::clear(self::$cachePrefix . $type . '_*');
        } else {
            Cache::clear(self::$cachePrefix . '*');
        }
        return ['code' => 1, 'msg' => '缓存清除成功'];
    }

    public static function getVodStatistics($type_id = 0, $start_time = 0, $end_time = 0)
    {
        $query = VodModel::where('vod_status', 1);

        if ($type_id > 0) {
            $query->where('type_id', $type_id);
        }

        if ($start_time > 0) {
            $query->where('vod_time', '>=', $start_time);
        }

        if ($end_time > 0) {
            $query->where('vod_time', '<=', $end_time);
        }

        $totalVods = $query->count();
        $totalHits = $query->sum('vod_hits');
        $totalUp = $query->sum('vod_up');
        $totalDown = $query->sum('vod_down');

        return [
            'code' => 1,
            'data' => [
                'total_vods' => $totalVods,
                'total_hits' => $totalHits,
                'total_up' => $totalUp,
                'total_down' => $totalDown
            ]
        ];
    }

    public static function importVod($data, $isUpdate = false)
    {
        Db::startTrans();
        try {
            if ($isUpdate && isset($data['vod_id'])) {
                $vod = VodModel::get($data['vod_id']);
                if ($vod) {
                    $vod->save($data);
                    $result = $vod;
                } else {
                    $result = VodModel::create($data);
                }
            } else {
                $result = VodModel::create($data);
            }

            Db::commit();
            self::clearCache();
            return ['code' => 1, 'msg' => '导入成功', 'data' => $result];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '导入失败: ' . $e->getMessage()];
        }
    }

    public static function batchImportVods($datas)
    {
        Db::startTrans();
        try {
            $results = [];
            foreach ($datas as $data) {
                $result = self::importVod($data);
                $results[] = $result;
            }

            Db::commit();
            return ['code' => 1, 'msg' => '批量导入成功', 'results' => $results];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '批量导入失败: ' . $e->getMessage()];
        }
    }
}
