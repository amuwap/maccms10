<?php
namespace app\api\controller;

use think\Controller;
use app\common\util\TemplateSettingService;

class Template extends Controller
{
    /**
     * 获取前端配置
     */
    public function frontend()
    {
        $config = TemplateSettingService::getFrontendConfig();
        return json(['code' => 1, 'data' => $config]);
    }

    /**
     * 获取APP配置
     */
    public function app()
    {
        $config = TemplateSettingService::getAppConfig();
        return json(['code' => 1, 'data' => $config]);
    }

    /**
     * 获取筛选配置
     */
    public function filter()
    {
        $params = input('get.');
        $config = TemplateSettingService::getFilterConfig();
        $options = TemplateSettingService::generateFilterOptions($params);
        
        return json([
            'code' => 1,
            'data' => [
                'config' => $config,
                'options' => $options
            ]
        ]);
    }

    /**
     * 获取排序配置
     */
    public function sort()
    {
        $config = TemplateSettingService::getSortConfig();
        return json(['code' => 1, 'data' => $config]);
    }

    /**
     * 智能排序
     */
    public function smartSort()
    {
        $param = input('post.');
        $items = $param['items'] ?? [];
        $sortBy = $param['sort_by'] ?? 'default';
        $userData = $param['user_data'] ?? [];
        
        $sortedItems = TemplateSettingService::smartSort($items, $sortBy, $userData);
        
        return json(['code' => 1, 'data' => $sortedItems]);
    }

    /**
     * 清除缓存
     */
    public function clearCache()
    {
        $type = input('get.type');
        TemplateSettingService::clearCache($type);
        return json(['code' => 1, 'msg' => '缓存已清除']);
    }

    /**
     * 获取语言配置
     */
    public function lang()
    {
        $lang = input('get.lang', 'zh');
        $frontendConfig = TemplateSettingService::getFrontendConfig();
        $langConfig = $frontendConfig['lang'][$lang] ?? $frontendConfig['lang']['zh'];
        
        return json(['code' => 1, 'data' => $langConfig]);
    }
}
