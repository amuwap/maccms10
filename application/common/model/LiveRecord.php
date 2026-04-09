<?php
namespace app\common\model;

use think\Db;
use think\Cache;

class LiveRecord extends Base {
    protected $name = 'live_record';
    protected $createTime = '';
    protected $updateTime = '';
    protected $auto = [];
    protected $insert = [];
    protected $update = [];

    const RECORD_TYPE_VIEW = 1; // 观看记录
    const RECORD_TYPE_GIFT = 2; // 礼物记录

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
        $list = Db::name('LiveRecord')->field($field)->where($where)->order($order)->limit($limit_str)->select();
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
        if(empty($data['live_id'])){
            return ['code'=>1001,'msg'=>'请填写直播ID'];
        }
        if(empty($data['user_id'])){
            return ['code'=>1001,'msg'=>'请填写用户ID'];
        }
        if(empty($data['record_type'])){
            return ['code'=>1001,'msg'=>'请填写记录类型'];
        }
        
        $data['record_time'] = time();
        $res = $this->allowField(true)->insert($data);
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

    public function addGiftRecord($liveId, $userId, $giftId, $giftNum = 1)
    {
        // 获取礼物信息
        $giftModel = new Gift();
        $giftInfo = $giftModel->infoData(['gift_id' => $giftId]);
        if($giftInfo['code'] != 1){
            return $giftInfo;
        }
        
        // 计算总金额
        $totalPrice = $giftInfo['info']['gift_price'] * $giftNum;
        
        // 保存礼物记录
        $data = [
            'live_id' => $liveId,
            'user_id' => $userId,
            'record_type' => self::RECORD_TYPE_GIFT,
            'record_gift_id' => $giftId,
            'record_gift_name' => $giftInfo['info']['gift_name'],
            'record_gift_num' => $giftNum,
            'record_amount' => $totalPrice
        ];
        
        return $this->saveData($data);
    }

    public function addViewRecord($liveId, $userId, $duration = 0)
    {
        $data = [
            'live_id' => $liveId,
            'user_id' => $userId,
            'record_type' => self::RECORD_TYPE_VIEW,
            'record_duration' => $duration
        ];
        
        return $this->saveData($data);
    }

    public function getGiftRecords($liveId, $limit = 20)
    {
        $where = [
            'live_id' => ['eq', $liveId],
            'record_type' => ['eq', self::RECORD_TYPE_GIFT]
        ];
        $order = 'record_time desc';
        return $this->listData($where, $order, 1, $limit, 0, '*', 0, 0);
    }

    public function getTotalGiftAmount($liveId)
    {
        $where = [
            'live_id' => ['eq', $liveId],
            'record_type' => ['eq', self::RECORD_TYPE_GIFT]
        ];
        return $this->where($where)->sum('record_amount') ?: 0;
    }
}
