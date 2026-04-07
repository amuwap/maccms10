<?php
namespace app\common\model;

use think\Db;
use think\Cache;

class Live extends Base {
    protected $name = 'live';
    protected $createTime = '';
    protected $updateTime = '';
    protected $auto = [];
    protected $insert = [];
    protected $update = [];

    const LIVE_STATUS_OFFLINE = 0;
    const LIVE_STATUS_ONLINE = 1;

    public function countData($where)
    {
        $total = $this->where($where)->count();
        return $total;
    }

    public function listData($where,$order,$page=1,$limit=20,$start=0,$field='*',$addition=1,$totalshow=1)
    {
        if(!is_array($where)){
            $where = json_decode($where,true);
        }
        $limit_str = ($limit * ($page-1) + $start) .",".$limit;
        if($totalshow==1) {
            $total = $this->where($where)->count();
        }
        $list = Db::name('Live')->field($field)->where($where)->order($order)->limit($limit_str)->select();
        return ['code'=>1,'msg'=>'数据列表','page'=>$page,'pagecount'=>ceil($total/$limit),'limit'=>$limit,'total'=>$total,'list'=>$list];
    }

    public function infoData($where,$field='*',$cache=0)
    {
        if(empty($where) || !is_array($where)){
            return ['code'=>1001,'msg'=>'参数错误'];
        }
        $info = $this->field($field)->where($where)->find();
        if (empty($info)) {
            return ['code' => 1002, 'msg' => '获取数据失败'];
        }
        $info = $info->toArray();
        return ['code'=>1,'msg'=>'获取成功','info'=>$info];
    }

    public function saveData($data)
    {
        if(empty($data['live_name'])){
            return ['code'=>1001,'msg'=>'请填写直播名称'];
        }
        if(empty($data['live_video_url'])){
            return ['code'=>1001,'msg'=>'请填写视频地址'];
        }
        
        if(!empty($data['live_id'])){
            $where=[];
            $where['live_id'] = ['eq',$data['live_id']];
            $data['live_time'] = time();
            $res = $this->allowField(true)->where($where)->update($data);
        }
        else{
            $data['live_time'] = time();
            $data['live_time_add'] = time();
            $res = $this->allowField(true)->insert($data);
        }
        if(false === $res){
            return ['code'=>1002,'msg'=>'保存失败：'.$this->getError() ];
        }
        return ['code'=>1,'msg'=>'保存成功'];
    }

    public function delData($where)
    {
        $res = $this->where($where)->delete();
        if($res===false){
            return ['code'=>1001,'msg'=>'删除失败：'.$this->getError() ];
        }
        return ['code'=>1,'msg'=>'删除成功'];
    }

    public function fieldData($where,$update)
    {
        if(!is_array($update)){
            return ['code'=>1001,'msg'=>'参数错误'];
        }
        $res = $this->allowField(true)->where($where)->update($update);
        if($res===false){
            return ['code'=>1001,'msg'=>'设置失败：'.$this->getError() ];
        }
        return ['code'=>1,'msg'=>'设置成功'];
    }

    public function updateAutoLiveStatus()
    {
        $now = time();
        $where = [];
        $where['live_is_auto'] = ['eq', 1];
        $list = $this->listData($where, 'live_id asc', 1, 100, 0, '*', 0, 0);
        
        foreach($list['list'] as $live) {
            $shouldBeOnline = false;
            if($live['live_start_time'] > 0 && $live['live_end_time'] > 0) {
                if($now >= $live['live_start_time'] && $now <= $live['live_end_time']) {
                    $shouldBeOnline = true;
                }
            }
            
            if($shouldBeOnline && $live['live_status'] != self::LIVE_STATUS_ONLINE) {
                $this->fieldData(['live_id' => $live['live_id']], ['live_status' => self::LIVE_STATUS_ONLINE]);
            } elseif(!$shouldBeOnline && $live['live_status'] != self::LIVE_STATUS_OFFLINE) {
                $this->fieldData(['live_id' => $live['live_id']], ['live_status' => self::LIVE_STATUS_OFFLINE]);
            }
        }
    }

    public function getOnlineList($limit = 20)
    {
        $this->updateAutoLiveStatus();
        $where = [];
        $where['live_status'] = ['eq', self::LIVE_STATUS_ONLINE];
        $order = 'live_sort asc, live_id desc';
        return $this->listData($where, $order, 1, $limit, 0, '*', 0, 0);
    }
}
