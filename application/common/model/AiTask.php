<?php
namespace app\common\model;

use think\Db;
use think\Cache;

class AiTask extends Base {
    protected $name = 'ai_task';
    protected $createTime = '';
    protected $updateTime = '';
    protected $auto = [];
    protected $insert = [];
    protected $update = [];

    const TASK_TYPE_BLURB = 1;
    const TASK_TYPE_ACTOR = 2;
    const TASK_TYPE_REVIEW = 3;
    const TASK_TYPE_PLOT = 4;
    const TASK_TYPE_SCORE = 5;
    const TASK_TYPE_ROLE = 6;

    const TASK_STATUS_PENDING = 0;
    const TASK_STATUS_PROCESSING = 1;
    const TASK_STATUS_COMPLETED = 2;
    const TASK_STATUS_FAILED = 3;

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
        $list = Db::name('AiTask')->field($field)->where($where)->order($order)->limit($limit_str)->select();
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
        if(empty($data['task_type'])){
            return ['code'=>1001,'msg'=>'请选择任务类型'];
        }
        if(empty($data['task_prompt'])){
            return ['code'=>1001,'msg'=>'请输入提示词'];
        }
        
        if(!empty($data['task_id'])){
            $where=[];
            $where['task_id'] = ['eq',$data['task_id']];
            $res = $this->allowField(true)->where($where)->update($data);
        }
        else{
            $data['task_time'] = time();
            $data['task_status'] = self::TASK_STATUS_PENDING;
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

    public function createTask($type, $mid, $rid, $prompt, $configId = 0)
    {
        $data = [
            'task_type' => $type,
            'task_mid' => $mid,
            'task_rid' => $rid,
            'task_prompt' => $prompt,
            'task_config_id' => $configId,
            'task_time' => time(),
            'task_status' => self::TASK_STATUS_PENDING
        ];
        return $this->saveData($data);
    }

    public function getPendingTasks($limit = 10)
    {
        $where = [];
        $where['task_status'] = ['eq', self::TASK_STATUS_PENDING];
        $order = 'task_id asc';
        return $this->listData($where, $order, 1, $limit, 0, '*', 0, 0);
    }
}
