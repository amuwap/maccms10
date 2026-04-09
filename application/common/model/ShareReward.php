<?php
namespace app\common\model;

use think\Db;
use think\Cache;

class ShareReward extends Base {
    protected $name = 'reward_log';
    protected $createTime = 'log_time';
    protected $updateTime = '';
    protected $auto = [];
    protected $insert = [];
    protected $update = [];

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
        $limit_str = ($limit * ($page-1) + $start) .",".$limit."";
        if($totalshow==1) {
            $total = $this->where($where)->count();
        }
        $list = $this->field($field)->where($where)->order($order)->limit($limit_str)->select();
        
        // 关联查询影片信息
        foreach($list as &$item){
            if($item['log_related_id']>0){
                $vodInfo = Db::name('Vod')->where('vod_id',$item['log_related_id'])->field('vod_name')->find();
                if($vodInfo){
                    $item['vod_name'] = $vodInfo['vod_name'];
                }
            }
        }
        
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
        if(empty($data['user_id'])){
            return ['code'=>1001,'msg'=>'请填写用户ID'];
        }
        if(empty($data['log_related_id'])){
            return ['code'=>1001,'msg'=>'请填写内容ID'];
        }
        
        if(!empty($data['log_id'])){
            $where=[];
            $where['log_id'] = ['eq',$data['log_id']];
            $res = $this->allowField(true)->where($where)->update($data);
        }
        else{
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
}
