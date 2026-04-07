<?php
namespace app\common\util;

use think\Cache;
use think\Db;
use app\common\model\Vod as VodModel;
use app\common\model\Art as ArtModel;
use app\common\model\Actor as ActorModel;

class SearchService
{
    protected static $cachePrefix = 'search_service_';
    protected static $cacheTime = 1800;

    public static function search($keyword, $type = 'vod', $page = 1, $limit = 20, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . md5($keyword . '_' . $type . '_' . $page . '_' . $limit);
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $result = null;
        switch ($type) {
            case 'vod':
                $result = self::searchVod($keyword, $page, $limit);
                break;
            case 'art':
                $result = self::searchArt($keyword, $page, $limit);
                break;
            case 'actor':
                $result = self::searchActor($keyword, $page, $limit);
                break;
            case 'all':
                $result = self::searchAll($keyword, $page, $limit);
                break;
            default:
                $result = self::searchVod($keyword, $page, $limit);
        }

        if ($use_cache && !empty($result)) {
            Cache::set($cacheKey, $result, self::$cacheTime);
        }

        return $result;
    }

    protected static function searchVod($keyword, $page = 1, $limit = 20)
    {
        $query = VodModel::where('vod_status', 1)
            ->where(function ($query) use ($keyword) {
                $query->where('vod_name', 'like', '%' . $keyword . '%')
                    ->whereOr('vod_actor', 'like', '%' . $keyword . '%')
                    ->whereOr('vod_director', 'like', '%' . $keyword . '%')
                    ->whereOr('vod_content', 'like', '%' . $keyword . '%')
                    ->whereOr('vod_remarks', 'like', '%' . $keyword . '%')
                    ->whereOr('vod_keywords', 'like', '%' . $keyword . '%');
            });

        $list = $query->order('vod_hits desc, vod_time desc')->page($page, $limit)->select();
        $total = $query->count();

        return [
            'code' => 1,
            'type' => 'vod',
            'keyword' => $keyword,
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    }

    protected static function searchArt($keyword, $page = 1, $limit = 20)
    {
        $query = ArtModel::where('art_status', 1)
            ->where(function ($query) use ($keyword) {
                $query->where('art_name', 'like', '%' . $keyword . '%')
                    ->whereOr('art_content', 'like', '%' . $keyword . '%');
            });

        $list = $query->order('art_hits desc, art_time desc')->page($page, $limit)->select();
        $total = $query->count();

        return [
            'code' => 1,
            'type' => 'art',
            'keyword' => $keyword,
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    }

    protected static function searchActor($keyword, $page = 1, $limit = 20)
    {
        $query = ActorModel::where('actor_status', 1)
            ->where(function ($query) use ($keyword) {
                $query->where('actor_name', 'like', '%' . $keyword . '%')
                    ->whereOr('actor_en_name', 'like', '%' . $keyword . '%');
            });

        $list = $query->order('actor_hits desc')->page($page, $limit)->select();
        $total = $query->count();

        return [
            'code' => 1,
            'type' => 'actor',
            'keyword' => $keyword,
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    }

    protected static function searchAll($keyword, $page = 1, $limit = 20)
    {
        $vodResult = self::searchVod($keyword, 1, intval($limit / 2));
        $artResult = self::searchArt($keyword, 1, intval($limit / 4));
        $actorResult = self::searchActor($keyword, 1, intval($limit / 4));

        return [
            'code' => 1,
            'type' => 'all',
            'keyword' => $keyword,
            'vod' => $vodResult,
            'art' => $artResult,
            'actor' => $actorResult
        ];
    }

    public static function getHotKeywords($type = 'vod', $limit = 20, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'hot_keywords_' . $type . '_' . $limit;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $keywords = [];
        switch ($type) {
            case 'vod':
                $vods = VodModel::where('vod_status', 1)
                    ->order('vod_hits desc')
                    ->limit($limit * 2)
                    ->select();
                
                foreach ($vods as $vod) {
                    $keywords[] = $vod['vod_name'];
                    if ($vod['vod_keywords']) {
                        $tags = explode(',', $vod['vod_keywords']);
                        foreach ($tags as $tag) {
                            $tag = trim($tag);
                            if ($tag && count($keywords) < $limit * 3) {
                                $keywords[] = $tag;
                            }
                        }
                    }
                }
                break;
            
            case 'art':
                $arts = ArtModel::where('art_status', 1)
                    ->order('art_hits desc')
                    ->limit($limit * 2)
                    ->select();
                
                foreach ($arts as $art) {
                    $keywords[] = $art['art_name'];
                }
                break;
        }

        $keywords = array_unique($keywords);
        $keywords = array_slice($keywords, 0, $limit);

        if ($use_cache && !empty($keywords)) {
            Cache::set($cacheKey, $keywords, 3600);
        }

        return $keywords;
    }

    public static function getSuggestions($keyword, $type = 'vod', $limit = 10)
    {
        if (empty($keyword)) {
            return [];
        }

        $result = self::search($keyword, $type, 1, $limit, false);
        
        $suggestions = [];
        if (isset($result['list']) && !empty($result['list'])) {
            foreach ($result['list'] as $item) {
                $nameField = $type == 'vod' ? 'vod_name' : ($type == 'art' ? 'art_name' : 'actor_name');
                if (isset($item[$nameField])) {
                    $suggestions[] = $item[$nameField];
                }
            }
        }

        return array_unique($suggestions);
    }

    public static function logSearch($user_id, $keyword, $type = 'vod')
    {
        try {
            $cacheKey = self::$cachePrefix . 'log_' . md5($user_id . '_' . $keyword . '_' . date('Ymd'));
            $hasLogged = Cache::get($cacheKey);
            
            if (!$hasLogged) {
                Cache::set($cacheKey, 1, 86400);
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
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

    public static function getSearchHistory($user_id, $limit = 20)
    {
        $cacheKey = self::$cachePrefix . 'history_' . $user_id;
        $history = Cache::get($cacheKey);
        
        if (!$history) {
            return [];
        }

        return array_slice($history, 0, $limit);
    }

    public static function addSearchHistory($user_id, $keyword)
    {
        $cacheKey = self::$cachePrefix . 'history_' . $user_id;
        $history = Cache::get($cacheKey, []);
        
        array_unshift($history, [
            'keyword' => $keyword,
            'time' => time()
        ]);
        
        $history = array_slice($history, 0, 50);
        Cache::set($cacheKey, $history, 2592000);
        
        return $history;
    }

    public static function clearSearchHistory($user_id)
    {
        $cacheKey = self::$cachePrefix . 'history_' . $user_id;
        Cache::clear($cacheKey);
        return ['code' => 1, 'msg' => '搜索历史清除成功'];
    }
}
