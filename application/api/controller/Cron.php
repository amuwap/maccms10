<?php
namespace app\api\controller;

use think\Controller;
use app\common\util\CronService;

class Cron extends Controller
{
    public function index()
    {
        $key = input('key');
        $config_key = config('cron.secret_key');
        
        if (!empty($config_key) && $key != $config_key) {
            return 'invalid key';
        }

        $action = input('action', 'all');
        $result = [];

        switch ($action) {
            case 'hot_keywords':
                $result['hot_keywords'] = CronService::collectHotKeywords();
                break;
            case 'live_control':
                $result['live_control'] = CronService::autoLiveControl();
                break;
            case 'sitemap':
                $result['sitemap'] = CronService::generateSitemap();
                break;
            case 'share_reward':
                $result['share_reward'] = CronService::shareReward();
                break;
            case 'all':
            default:
                $result['hot_keywords'] = CronService::collectHotKeywords();
                $result['live_control'] = CronService::autoLiveControl();
                $result['sitemap'] = CronService::generateSitemap();
                $result['share_reward'] = CronService::shareReward();
                break;
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
