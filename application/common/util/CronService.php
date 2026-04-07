<?php
namespace app\common\util;

use app\common\model\HotKeyword as HotKeywordModel;
use app\common\model\Live as LiveModel;
use think\Db;

class CronService
{
    public static function collectHotKeywords()
    {
        $config = \app\common\model\AiConfig::where('config_is_default', 1)->find();
        if (!$config) {
            return ['code' => 0, 'msg' => '未设置默认AI配置'];
        }

        $ai_service = new AiService($config);
        $prompt = '请列出当前中国最热门的20部电影和20部电视剧的标题，只返回标题，每行一个，不要其他内容。';
        
        try {
            $result = $ai_service->call($prompt);
            if ($result['code'] == 1) {
                $titles = explode("\n", trim($result['content']));
                $titles = array_filter($titles);
                
                Db::startTrans();
                try {
                    HotKeywordModel::where('keyword_id', '>', 0)->delete();
                    
                    foreach ($titles as $title) {
                        $title = trim($title);
                        if (!empty($title)) {
                            HotKeywordModel::create([
                                'keyword_title' => $title,
                                'keyword_sort' => 0,
                                'keyword_create_time' => time()
                            ]);
                        }
                    }
                    
                    Db::commit();
                    return ['code' => 1, 'msg' => '热门关键词收集成功', 'count' => count($titles)];
                } catch (\Exception $e) {
                    Db::rollback();
                    return ['code' => 0, 'msg' => '保存失败: ' . $e->getMessage()];
                }
            } else {
                return $result;
            }
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => '收集失败: ' . $e->getMessage()];
        }
    }

    public static function autoLiveControl()
    {
        $now = time();
        $lives = LiveModel::where('live_status', 0)->select();
        
        foreach ($lives as $live) {
            if (!empty($live['live_schedule_start'])) {
                $start_time = strtotime($live['live_schedule_start']);
                if ($start_time <= $now && $live['live_status'] == 0) {
                    LiveModel::update(['live_status' => 1], ['live_id' => $live['live_id']]);
                }
            }
        }

        $lives = LiveModel::where('live_status', 1)->select();
        foreach ($lives as $live) {
            if (!empty($live['live_schedule_end'])) {
                $end_time = strtotime($live['live_schedule_end']);
                if ($end_time <= $now && $live['live_status'] == 1) {
                    LiveModel::update(['live_status' => 0], ['live_id' => $live['live_id']]);
                }
            }
        }

        return ['code' => 1, 'msg' => '直播自动控制完成'];
    }

    public static function generateSitemap()
    {
        $domain = config('site.site_url');
        if (empty($domain)) {
            $domain = 'http://' . $_SERVER['HTTP_HOST'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . $domain . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>daily</changefreq>' . "\n";
        $xml .= '    <priority>1.0</priority>' . "\n";
        $xml .= '  </url>' . "\n";

        $vods = \app\common\model\Vod::where('vod_status', 1)->order('vod_id desc')->limit(1000)->select();
        foreach ($vods as $vod) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $domain . '/index.php/vod/detail/id/' . $vod['vod_id'] . '.html</loc>' . "\n";
            $xml .= '    <lastmod>' . date('Y-m-d', $vod['vod_time']) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.8</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $arts = \app\common\model\Art::where('art_status', 1)->order('art_id desc')->limit(500)->select();
        foreach ($arts as $art) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $domain . '/index.php/art/detail/id/' . $art['art_id'] . '.html</loc>' . "\n";
            $xml .= '    <lastmod>' . date('Y-m-d', $art['art_time']) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.6</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        file_put_contents('./sitemap.xml', $xml);

        return ['code' => 1, 'msg' => 'XML地图生成成功'];
    }

    public static function shareReward()
    {
        $rewards = \app\common\model\ShareReward::where('reward_status', 0)->select();
        
        foreach ($rewards as $reward) {
            if ($reward['reward_click_count'] >= $reward['reward_target_count']) {
                Db::startTrans();
                try {
                    \app\common\model\User::update([
                        'user_points' => ['exp', 'user_points+' . $reward['reward_points']]
                    ], ['user_id' => $reward['user_id']]);

                    \app\common\model\ShareReward::update([
                        'reward_status' => 1,
                        'reward_receive_time' => time()
                    ], ['reward_id' => $reward['reward_id']]);

                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                }
            }
        }

        return ['code' => 1, 'msg' => '分享奖励处理完成'];
    }
}
