<?php
namespace app\index\controller;
use think\Controller;

class Vod extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->label_fetch('vod/index');
    }

    public function type()
    {
        $info = $this->label_type();
        return $this->label_fetch( mac_tpl_fetch('vod',$info['type_tpl'],'type') );
    }

    public function show()
    {
        $info = $this->label_type();
        return $this->label_fetch( mac_tpl_fetch('vod',$info['type_tpl_list'],'show') );
    }

    public function ajax_show()
    {
        $info = $this->label_type();
        return $this->label_fetch('vod/ajax_show');
    }

    public function search()
    {
        $param = mac_param_url();
        $this->check_search($param);
        $this->assign('param',$param);
        return $this->label_fetch('vod/search');
    }

    public function ajax_search()
    {
        $param = mac_param_url();
        $this->check_search($param);
        $this->assign('param',$param);
        return $this->label_fetch('vod/ajax_search');
    }

    public function detail()
    {
        $info = $this->label_vod_detail();
        if($info['vod_copyright']==1 && !empty($info['vod_jumpurl']) && $GLOBALS['config']['app']['copyright_status']==2){
            return $this->label_fetch('vod/copyright');
        }
        if(!empty($info['vod_pwd']) && session('1-1-'.$info['vod_id'])!='1'){
            return $this->label_fetch('vod/detail_pwd');
        }
        return $this->label_fetch( mac_tpl_fetch('vod',$info['vod_tpl'],'detail') );
    }

    public function ajax_detail()
    {
        $info = $this->label_vod_detail();
        return $this->label_fetch('vod/ajax_detail');
    }

    public function copyright()
    {
        $info = $this->label_vod_detail();
        return $this->label_fetch('vod/copyright');
    }

    public function role()
    {
        $info = $this->label_vod_role();
        return $this->label_fetch('vod/role');
    }

    public function play()
    {
        $info = $this->label_vod_play('play');
        if($info['vod_copyright']==1 && $GLOBALS['config']['app']['copyright_status']==3){
            return $this->label_fetch('vod/copyright');
        }
        return $this->label_fetch( mac_tpl_fetch('vod',$info['vod_tpl_play'],'play') );
    }

    public function player()
    {
        $info = $this->label_vod_play('play',[],0,1);
        if($info['vod_copyright']==1 && $GLOBALS['config']['app']['copyright_status']==4){
            return $this->label_fetch('vod/copyright');
        }
        if(!empty($info['vod_pwd_play']) && session('1-4-'.$info['vod_id'])!='1'){
            return $this->label_fetch('vod/player_pwd');
        }
        return $this->label_fetch('vod/player');
    }

    public function down()
    {
        $info = $this->label_vod_play('down');
        return $this->label_fetch( mac_tpl_fetch('vod',$info['vod_tpl_down'],'down') );
    }

    public function downer()
    {
        $info = $this->label_vod_play('down');
        if(!empty($info['vod_pwd_down']) && session('1-5-'.$info['vod_id'])!='1'){
            return $this->label_fetch('vod/downer_pwd');
        }
        return $this->label_fetch('vod/downer');
    }

    public function rss()
    {
        $info = $this->label_vod_detail();
        return $this->label_fetch('vod/rss');
    }

    public function plot()
    {
        $info = $this->label_vod_detail();
        return $this->label_fetch('vod/plot');
    }

    // 记录分享并给予奖励
    public function share()
    {
        $vodId = input('post.vod_id/d');
        $sharePlatform = input('post.share_platform/s', 'system');
        
        if(!$vodId){
            return json(['code' => 0, 'msg' => '参数错误']);
        }
        
        // 检查用户是否登录
        if(!session('user_id')){
            return json(['code' => 0, 'msg' => '请先登录']);
        }
        
        $userId = session('user_id');
        
        // 实例化模型
        $shareRewardModel = new \app\common\model\ShareReward();
        
        // 检查今天是否已经分享过
        $today = date('Y-m-d');
        $todayShares = $shareRewardModel->where('user_id', $userId)->where('share_date', $today)->count();
        
        if($todayShares >= 10){ // 每天最多分享10次
            return json(['code' => 0, 'msg' => '今日分享次数已达上限']);
        }
        
        // 检查是否已经分享过该影片
        $existShare = $shareRewardModel->where('user_id', $userId)->where('vod_id', $vodId)->where('share_date', $today)->find();
        if($existShare){
            return json(['code' => 0, 'msg' => '今日已分享过该影片']);
        }
        
        // 生成随机奖励（1-10积分）
        $reward = mt_rand(1, 10);
        
        // 开启事务
        $shareRewardModel->startTrans();
        
        try {
            // 记录分享
            $shareData = [
                'user_id' => $userId,
                'log_related_id' => $vodId,
                'log_type' => 2, // 2表示分享奖励
                'log_points' => $reward,
                'log_remark' => $sharePlatform,
                'log_time' => time()
            ];
            $shareRewardModel->save($shareData);
            
            // 更新用户积分
            $userModel = new \app\common\model\User();
            $userModel->where('user_id', $userId)->setInc('user_points', $reward);
            
            // 提交事务
            $shareRewardModel->commit();
            
            return json(['code' => 1, 'msg' => '分享成功', 'data' => ['reward' => $reward]]);
        } catch (\Exception $e) {
            // 回滚事务
            $shareRewardModel->rollback();
            return json(['code' => 0, 'msg' => '分享失败']);
        }
    }

    // 报告播放错误
    public function report_error()
    {
        $vodId = input('post.vod_id/d');
        $errorContent = input('post.error_content/s');
        
        if(!$vodId || !$errorContent){
            return json(['code' => 0, 'msg' => '参数错误']);
        }
        
        // 记录错误报告
        $playErrorModel = new \app\common\model\PlayError();
        $errorData = [
            'user_id' => session('user_id') ?: 0,
            'vod_id' => $vodId,
            'error_content' => $errorContent,
            'create_time' => time()
        ];
        
        if($playErrorModel->save($errorData)){
            return json(['code' => 1, 'msg' => '报错成功，感谢您的反馈']);
        } else {
            return json(['code' => 0, 'msg' => '报错失败']);
        }
    }

    // 求片功能
    public function request_film()
    {
        $requestName = input('post.request_name/s');
        $requestContent = input('post.request_content/s');
        
        if(!$requestName || !$requestContent){
            return json(['code' => 0, 'msg' => '参数错误']);
        }
        
        // 记录求片请求
        $requestFilmModel = new \app\common\model\RequestFilm();
        $requestData = [
            'user_id' => session('user_id') ?: 0,
            'req_name' => $requestName,
            'req_content' => $requestContent,
            'req_status' => 0,
            'req_time' => time()
        ];
        
        if($requestFilmModel->save($requestData)){
            return json(['code' => 1, 'msg' => '求片成功，我们会尽快处理']);
        } else {
            return json(['code' => 0, 'msg' => '求片失败']);
        }
    }

}
