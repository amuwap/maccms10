<?php
namespace app\common\util;

class TemplateSettingService {
    private static $config = null;
    
    /**
     * 获取模板配置
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($key = null, $default = null) {
        if (self::$config === null) {
            self::loadConfig();
        }
        
        if ($key === null) {
            return self::$config;
        }
        
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    /**
     * 加载配置文件
     */
    private static function loadConfig() {
        $configFile = APP_PATH . 'extra/template.php';
        if (file_exists($configFile)) {
            self::$config = include $configFile;
        } else {
            self::$config = [];
        }
    }
    
    /**
     * 获取前端配置
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getFrontend($key = null, $default = null) {
        return self::get('frontend' . ($key ? '.' . $key : ''), $default);
    }
    
    /**
     * 获取APP配置
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getApp($key = null, $default = null) {
        return self::get('app' . ($key ? '.' . $key : ''), $default);
    }
    
    /**
     * 获取筛选配置
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getFilter($key = null, $default = null) {
        return self::get('filter' . ($key ? '.' . $key : ''), $default);
    }
    
    /**
     * 获取排序配置
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getSort($key = null, $default = null) {
        return self::get('sort' . ($key ? '.' . $key : ''), $default);
    }
    
    /**
     * 获取主题颜色
     * @param string $color 颜色名称
     * @param string $default 默认值
     * @return string
     */
    public static function getColor($color = 'primary', $default = '#3b82f6') {
        return self::getFrontend('theme_color.' . $color, $default);
    }
    
    /**
     * 获取按钮文字
     * @param string $type 按钮类型
     * @param string $default 默认值
     * @return string
     */
    public static function getBtnText($type = 'play', $default = '播放') {
        return self::getFrontend('btn_text.' . $type, $default);
    }
    
    /**
     * 获取状态文字
     * @param string $type 状态类型
     * @param string $default 默认值
     * @return string
     */
    public static function getStatusText($type = 'vip', $default = 'VIP') {
        return self::getFrontend('status_text.' . $type, $default);
    }
    
    /**
     * 获取语言文字
     * @param string $key 语言键名
     * @param string $default 默认值
     * @param string $lang 语言代码
     * @return string
     */
    public static function getLang($key, $default = '', $lang = 'zh') {
        $text = self::getFrontend('lang.' . $lang . '.' . $key, $default);
        // 替换变量
        $text = str_replace('{site_name}', config('maccms.site.site_name'), $text);
        return $text;
    }
    
    /**
     * 检查功能是否启用
     * @param string $feature 功能名称
     * @return bool
     */
    public static function isFeatureEnabled($feature) {
        return self::getFrontend('features.' . $feature, true);
    }
    
    /**
     * 获取首页配置
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getHomepage($key, $default = null) {
        return self::getFrontend('homepage.' . $key, $default);
    }
    
    /**
     * 获取影视详情页配置
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getVodDetail($key, $default = null) {
        return self::getFrontend('vod_detail.' . $key, $default);
    }
    
    /**
     * 获取播放页面配置
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getVodPlay($key, $default = null) {
        return self::getFrontend('vod_play.' . $key, $default);
    }
    
    /**
     * 获取直播页面配置
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getLive($key, $default = null) {
        return self::getFrontend('live.' . $key, $default);
    }
}
