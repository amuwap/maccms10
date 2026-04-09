<?php
namespace app\common\model;

use think\Db;
use think\Cache;

class RequestFilm extends Base {
    protected $name = 'request';
    protected $createTime = 'req_time';
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
        
        // 转换字段名以便前端使用
        foreach($list as &$item){
            $item['request_id'] = $item['req_id'];
            $item['request_name'] = $item['req_name'];
            $item['request_content'] = $item['req_content'];
            $item['request_status'] = $item['req_status'];
            $item['create_time'] = $item['req_time'];
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
        if(empty($data['req_name'])){
            return ['code'=>1001,'msg'=>'请填写影片名称'];
        }
        if(empty($data['req_content'])){
            return ['code'=>1001,'msg'=>'请填写求片内容'];
        }
        
        if(!empty($data['req_id'])){
            $where=[];
            $where['req_id'] = ['eq',$data['req_id']];
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
