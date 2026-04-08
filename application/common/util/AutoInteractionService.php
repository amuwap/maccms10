<?php
namespace app\common\util;

use app\common\model\User;
use app\common\model\Comment;
use app\common\model\Vod;
use app\common\model\Live;

class AutoInteractionService {
    /**
     * 批量注册用户
     * @param array $params 注册参数
     * @return array
     */
    public static function batchRegisterUsers($params) {
        return model('User')->batchRegister($params);
    }
    
    /**
     * 获取批量注册的用户列表
     * @param int $limit 数量限制
     * @return array
     */
    public static function getBatchUsers($limit = 50) {
        $where = [];
        $where['user_reg_ip'] = ['eq', sprintf('%u', ip2long('127.0.0.1'))];
        $order = 'user_id desc';
        $res = model('User')->listData($where, $order, 1, $limit);
        return $res['list'];
    }
    
    /**
     * 自动在影视评论区发布评论
     * @param int $vod_id 影视ID
     * @param int $user_id 用户ID
     * @param string $content 评论内容
     * @return array
     */
    public static function autoCommentVod($vod_id, $user_id, $content) {
        $vod_info = model('Vod')->infoData(['vod_id' => $vod_id]);
        if ($vod_info['code'] > 1) {
            return ['code' => 1001, 'msg' => '影视不存在'];
        }
        
        $user_info = model('User')->infoData(['user_id' => $user_id]);
        if ($user_info['code'] > 1) {
            return ['code' => 1002, 'msg' => '用户不存在'];
        }
        
        $data = [
            'comment_mid' => 1, // 影视模块
            'comment_rid' => $vod_id,
            'user_id' => $user_id,
            'comment_name' => $user_info['info']['user_name'],
            'comment_content' => $content,
            'comment_time' => time(),
            'comment_status' => 1, // 直接启用
            'comment_ip' => sprintf('%u', ip2long('127.0.0.1'))
        ];
        
        return model('Comment')->saveData($data);
    }
    
    /**
     * 自动在直播间发布弹幕
     * @param int $live_id 直播ID
     * @param int $user_id 用户ID
     * @param string $content 弹幕内容
     * @return array
     */
    public static function autoCommentLive($live_id, $user_id, $content) {
        $live_info = model('Live')->infoData(['live_id' => $live_id]);
        if ($live_info['code'] > 1) {
            return ['code' => 1001, 'msg' => '直播不存在'];
        }
        
        $user_info = model('User')->infoData(['user_id' => $user_id]);
        if ($user_info['code'] > 1) {
            return ['code' => 1002, 'msg' => '用户不存在'];
        }
        
        $data = [
            'comment_mid' => 9, // 直播模块
            'comment_rid' => $live_id,
            'user_id' => $user_id,
            'comment_name' => $user_info['info']['user_name'],
            'comment_content' => $content,
            'comment_time' => time(),
            'comment_status' => 1, // 直接启用
            'comment_ip' => sprintf('%u', ip2long('127.0.0.1'))
        ];
        
        return model('Comment')->saveData($data);
    }
    
    /**
     * 批量自动评论影视
     * @param int $vod_id 影视ID
     * @param array $user_ids 用户ID列表
     * @param array $contents 评论内容列表
     * @return array
     */
    public static function batchAutoCommentVod($vod_id, $user_ids, $contents) {
        $success_count = 0;
        $failed_count = 0;
        $failed_reasons = [];
        
        foreach ($user_ids as $user_id) {
            $content = $contents[array_rand($contents)];
            $res = self::autoCommentVod($vod_id, $user_id, $content);
            if ($res['code'] == 1) {
                $success_count++;
            } else {
                $failed_count++;
                $failed_reasons[] = "用户 {$user_id}：{$res['msg']}";
            }
            // 随机延迟，模拟真实用户行为
            usleep(rand(100000, 500000));
        }
        
        return [
            'code' => 1,
            'msg' => "批量评论完成，成功：{$success_count}，失败：{$failed_count}",
            'success_count' => $success_count,
            'failed_count' => $failed_count,
            'failed_reasons' => $failed_reasons
        ];
    }
    
    /**
     * 批量自动评论直播
     * @param int $live_id 直播ID
     * @param array $user_ids 用户ID列表
     * @param array $contents 弹幕内容列表
     * @return array
     */
    public static function batchAutoCommentLive($live_id, $user_ids, $contents) {
        $success_count = 0;
        $failed_count = 0;
        $failed_reasons = [];
        
        foreach ($user_ids as $user_id) {
            $content = $contents[array_rand($contents)];
            $res = self::autoCommentLive($live_id, $user_id, $content);
            if ($res['code'] == 1) {
                $success_count++;
            } else {
                $failed_count++;
                $failed_reasons[] = "用户 {$user_id}：{$res['msg']}";
            }
            // 随机延迟，模拟真实用户行为
            usleep(rand(100000, 500000));
        }
        
        return [
            'code' => 1,
            'msg' => "批量弹幕完成，成功：{$success_count}，失败：{$failed_count}",
            'success_count' => $success_count,
            'failed_count' => $failed_count,
            'failed_reasons' => $failed_reasons
        ];
    }
    
    /**
     * 生成AI评论内容
     * @param string $vod_title 影视标题
     * @param string $vod_content 影视内容
     * @return string
     */
    public static function generateAIComment($vod_title, $vod_content = '') {
        // 这里可以集成AI接口生成评论
        // 暂时使用预设的评论模板
        $templates = [
            "{$vod_title}真的太好看了，强烈推荐！",
            "剧情很精彩，演员表现出色，值得一看。",
            "画面制作精良，音效震撼，是一部佳作。",
            "故事情节紧凑，引人入胜，根本停不下来。",
            "演员的演技太棒了，完全沉浸在剧情中。",
            "这部作品的特效做得非常逼真，视觉效果震撼。",
            "剧情设计巧妙，结局出乎意料，值得回味。",
            "音乐配得很好，增强了剧情的感染力。",
            "导演的手法很独特，给人耳目一新的感觉。",
            "这部作品传递了很多正能量，很有教育意义。"
        ];
        
        return $templates[array_rand($templates)];
    }
}
