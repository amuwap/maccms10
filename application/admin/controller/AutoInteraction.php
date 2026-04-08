<?php
namespace app\admin\controller;
use think\Controller;
use app\common\util\AutoInteractionService;

class AutoInteraction extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->assign('title', '自动互动管理');
        return $this->fetch('admin@auto_interaction/index');
    }

    public function batchRegister()
    {
        if (request()->isPost()) {
            $param = input();
            $res = AutoInteractionService::batchRegisterUsers($param);
            return json($res);
        }
        
        $group_list = model('Group')->getCache('group_list');
        $this->assign('group_list', $group_list);
        $this->assign('title', '批量注册用户');
        return $this->fetch('admin@auto_interaction/batch_register');
    }

    public function autoCommentVod()
    {
        if (request()->isPost()) {
            $param = input();
            $vod_id = intval($param['vod_id']);
            $user_count = intval($param['user_count']);
            $content_type = $param['content_type'];
            
            // 获取批量注册的用户
            $users = AutoInteractionService::getBatchUsers($user_count);
            if (empty($users)) {
                return json(['code' => 1001, 'msg' => '没有找到批量注册的用户']);
            }
            
            $user_ids = array_column($users, 'user_id');
            
            // 准备评论内容
            if ($content_type == 'ai') {
                $vod_info = model('Vod')->infoData(['vod_id' => $vod_id]);
                if ($vod_info['code'] > 1) {
                    return json(['code' => 1002, 'msg' => '影视不存在']);
                }
                $contents = [];
                for ($i = 0; $i < 10; $i++) {
                    $contents[] = AutoInteractionService::generateAIComment($vod_info['info']['vod_name']);
                }
            } else {
                $contents = explode('\n', $param['custom_content']);
                $contents = array_filter($contents, function($item) {
                    return trim($item) != '';
                });
                if (empty($contents)) {
                    return json(['code' => 1003, 'msg' => '请填写评论内容']);
                }
            }
            
            // 执行批量评论
            $res = AutoInteractionService::batchAutoCommentVod($vod_id, $user_ids, $contents);
            return json($res);
        }
        
        $this->assign('title', '自动评论影视');
        return $this->fetch('admin@auto_interaction/auto_comment_vod');
    }

    public function autoCommentLive()
    {
        if (request()->isPost()) {
            $param = input();
            $live_id = intval($param['live_id']);
            $user_count = intval($param['user_count']);
            $contents = explode('\n', $param['danmaku_content']);
            $contents = array_filter($contents, function($item) {
                return trim($item) != '';
            });
            
            if (empty($contents)) {
                return json(['code' => 1001, 'msg' => '请填写弹幕内容']);
            }
            
            // 获取批量注册的用户
            $users = AutoInteractionService::getBatchUsers($user_count);
            if (empty($users)) {
                return json(['code' => 1002, 'msg' => '没有找到批量注册的用户']);
            }
            
            $user_ids = array_column($users, 'user_id');
            
            // 执行批量弹幕
            $res = AutoInteractionService::batchAutoCommentLive($live_id, $user_ids, $contents);
            return json($res);
        }
        
        $this->assign('title', '自动弹幕直播');
        return $this->fetch('admin@auto_interaction/auto_comment_live');
    }

    public function userList()
    {
        $users = AutoInteractionService::getBatchUsers(100);
        $this->assign('users', $users);
        $this->assign('title', '批量注册用户列表');
        return $this->fetch('admin@auto_interaction/user_list');
    }
}
