<?php
namespace app\common\model;

use think\Db;
use think\Cache;

class Payment extends Base {
    protected $name = 'payment';
    protected $createTime = '';
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
        $limit_str = ($limit * ($page-1) + $start) .",".$limit;
        if($totalshow==1) {
            $total = $this->where($where)->count();
        }
        $list = Db::name('Payment')->field($field)->where($where)->order($order)->limit($limit_str)->select();
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
        if(empty($data['pay_name'])){
            return ['code'=>1001,'msg'=>'请填写支付方式名称'];
        }
        if(empty($data['pay_code'])){
            return ['code'=>1001,'msg'=>'请填写支付方式代码'];
        }
        
        if(!empty($data['pay_id'])){
            $where=[];
            $where['pay_id'] = ['eq',$data['pay_id']];
            $data['pay_time'] = time();
            $res = $this->allowField(true)->where($where)->update($data);
        }
        else{
            $data['pay_time'] = time();
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

    public function getActivePayments()
    {
        $where = [];
        $where['pay_status'] = ['eq', 1];
        $order = 'pay_sort asc, pay_id asc';
        $list = $this->listData($where, $order, 1, 100, 0, '*', 0, 0);
        return $list['list'];
    }

    public function getPaymentByCode($code)
    {
        $where = [];
        $where['pay_code'] = ['eq', $code];
        $where['pay_status'] = ['eq', 1];
        $info = $this->infoData($where);
        if($info['code'] == 1){
            return $info['info'];
        }
        return null;
    }
}
