<?php
namespace app\common\util;

use think\Cache;

class TemplateSettingService
{
    protected static $cachePrefix = 'template_setting_';
    protected static $cacheTime = 3600;

    /**
     * 获取模板设置
     * @param string $type 类型：frontend, app, filter, sort
     * @param string $key 键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($type = 'frontend', $key = null, $default = null)
    {
        $cacheKey = self::$cachePrefix . $type;
        $config = Cache::get($cacheKey);
        
        if (!$config) {
            $config = config('template.' . $type);
            Cache::set($cacheKey, $config, self::$cacheTime);
        }
        
        if ($key === null) {
            return $config;
        }
        
        return self::getNestedValue($config, $key, $default);
    }

    /**
     * 获取嵌套值
     * @param array $array 数组
     * @param string $key 键名，支持点号分隔
     * @param mixed $default 默认值
     * @return mixed
     */
    protected static function getNestedValue($array, $key, $default)
    {
        $keys = explode('.', $key);
        $value = $array;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }

    /**
     * 清除缓存
     * @param string $type 类型
     */
    public static function clearCache($type = null)
    {
        if ($type) {
            Cache::clear(self::$cachePrefix . $type);
        } else {
            Cache::clear(self::$cachePrefix . '*');
        }
    }

    /**
     * 获取前端配置
     * @return array
     */
    public static function getFrontendConfig()
    {
        return self::get('frontend');
    }

    /**
     * 获取APP配置
     * @return array
     */
    public static function getAppConfig()
    {
        return self::get('app');
    }

    /**
     * 获取筛选配置
     * @return array
     */
    public static function getFilterConfig()
    {
        return self::get('filter');
    }

    /**
     * 获取排序配置
     * @return array
     */
    public static function getSortConfig()
    {
        return self::get('sort');
    }

    /**
     * 智能排序
     * @param array $items 项目列表
     * @param string $sortBy 排序方式
     * @param array $userData 用户数据（用于智能推荐）
     * @return array
     */
    public static function smartSort($items, $sortBy = 'default', $userData = [])
    {
        $sortConfig = self::getSortConfig();
        
        if ($sortBy === 'default') {
            $sortBy = $sortConfig['default'];
        }
        
        // 智能推荐排序
        if ($sortBy === 'smart' && $sortConfig['smart']) {
            return self::sortBySmart($items, $userData, $sortConfig['smart_options']);
        }
        
        // 常规排序
        switch ($sortBy) {
            case 'time':
                usort($items, function($a, $b) {
                    return $b['vod_time'] <=> $a['vod_time'];
                });
                break;
            case 'hits':
                usort($items, function($a, $b) {
                    return ($b['vod_hits'] ?? 0) <=> ($a['vod_hits'] ?? 0);
                });
                break;
            case 'score':
                usort($items, function($a, $b) {
                    return ($b['vod_score'] ?? 0) <=> ($a['vod_score'] ?? 0);
                });
                break;
            case 'rand':
                shuffle($items);
                break;
        }
        
        return $items;
    }

    /**
     * 智能推荐排序
     * @param array $items 项目列表
     * @param array $userData 用户数据
     * @param array $options 选项
     * @return array
     */
    protected static function sortBySmart($items, $userData, $options)
    {
        $weight = $options['weight'] ?? [
            'view' => 0.4,
            'collect' => 0.3,
            'comment' => 0.2,
            'like' => 0.1
        ];
        
        foreach ($items as &$item) {
            $score = 0;
            
            // 基于用户行为的评分
            if (!empty($userData)) {
                // 计算用户对该类型内容的偏好
                $categoryScore = self::calculateCategoryScore($item, $userData);
                $score += $categoryScore * 0.5;
            }
            
            // 基于内容本身的评分
            $score += ($item['vod_hits'] ?? 0) * 0.2;
            $score += ($item['vod_score'] ?? 0) * 0.2;
            $score += ($item['vod_comment'] ?? 0) * 0.1;
            
            $item['_smart_score'] = $score;
        }
        
        // 按智能评分排序
        usort($items, function($a, $b) {
            return ($b['_smart_score'] ?? 0) <=> ($a['_smart_score'] ?? 0);
        });
        
        return $items;
    }

    /**
     * 计算分类评分
     * @param array $item 项目
     * @param array $userData 用户数据
     * @return float
     */
    protected static function calculateCategoryScore($item, $userData)
    {
        $score = 0;
        $userCategories = $userData['categories'] ?? [];
        
        if (!empty($userCategories) && isset($item['type_id'])) {
            $itemCategory = $item['type_id'];
            if (isset($userCategories[$itemCategory])) {
                $score = $userCategories[$itemCategory] / max(array_values($userCategories));
            }
        }
        
        return $score;
    }

    /**
     * 生成筛选选项
     * @param array $params 请求参数
     * @return array
     */
    public static function generateFilterOptions($params = [])
    {
        $filterConfig = self::getFilterConfig();
        $options = [];
        
        foreach ($filterConfig['options'] as $option) {
            switch ($option) {
                case 'type':
                    $options['type'] = self::getCategoryOptions();
                    break;
                case 'area':
                    $options['area'] = self::getAreaOptions();
                    break;
                case 'year':
                    $options['year'] = self::getYearOptions();
                    break;
                case 'lang':
                    $options['lang'] = self::getLangOptions();
                    break;
                case 'state':
                    $options['state'] = self::getStateOptions();
                    break;
            }
        }
        
        return $options;
    }

    /**
     * 获取分类选项
     * @return array
     */
    protected static function getCategoryOptions()
    {
        // 这里应该从数据库获取分类列表
        return [
            ['id' => '', 'name' => '全部分类'],
            ['id' => '1', 'name' => '电影'],
            ['id' => '2', 'name' => '电视剧'],
            ['id' => '3', 'name' => '动漫'],
            ['id' => '4', 'name' => '综艺'],
            ['id' => '5', 'name' => '纪录片']
        ];
    }

    /**
     * 获取地区选项
     * @return array
     */
    protected static function getAreaOptions()
    {
        return [
            ['id' => '', 'name' => '全部地区'],
            ['id' => 'cn', 'name' => '中国大陆'],
            ['id' => 'hk', 'name' => '中国香港'],
            ['id' => 'tw', 'name' => '中国台湾'],
            ['id' => 'jp', 'name' => '日本'],
            ['id' => 'kr', 'name' => '韩国'],
            ['id' => 'us', 'name' => '美国'],
            ['id' => 'eu', 'name' => '欧洲'],
            ['id' => 'other', 'name' => '其他']
        ];
    }

    /**
     * 获取年份选项
     * @return array
     */
    protected static function getYearOptions()
    {
        $years = [];
        $currentYear = date('Y');
        $years[] = ['id' => '', 'name' => '全部年份'];
        
        for ($i = $currentYear; $i >= 2000; $i--) {
            $years[] = ['id' => (string)$i, 'name' => $i . '年'];
        }
        
        return $years;
    }

    /**
     * 获取语言选项
     * @return array
     */
    protected static function getLangOptions()
    {
        return [
            ['id' => '', 'name' => '全部语言'],
            ['id' => 'zh', 'name' => '国语'],
            ['id' => 'en', 'name' => '英语'],
            ['id' => 'jp', 'name' => '日语'],
            ['id' => 'kr', 'name' => '韩语'],
            ['id' => 'other', 'name' => '其他']
        ];
    }

    /**
     * 获取状态选项
     * @return array
     */
    protected static function getStateOptions()
    {
        return [
            ['id' => '', 'name' => '全部状态'],
            ['id' => '1', 'name' => '连载中'],
            ['id' => '2', 'name' => '已完结'],
            ['id' => '3', 'name' => '即将上映']
        ];
    }
}
