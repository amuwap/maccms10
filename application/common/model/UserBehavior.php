<?php
namespace app\common\model;

use think\Db;
use think\Cache;

class UserBehavior extends Base {
    protected $name = 'user_behavior';
    protected $createTime = '';
    protected $updateTime = '';
    protected $auto = [];
    protected $insert = [];
    protected $update = [];

    const TYPE_HISTORY = 1;
    const TYPE_FAVORITE = 2;
    const TYPE_FOLLOW = 3;
    const TYPE_LIKE = 4;

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
        $list = Db::name('UserBehavior')->field($field)->where($where)->order($order)->limit($limit_str)->select();
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
            return ['code'=>1001,'msg'=>'请选择用户'];
        }
        if(empty($data['bh_type'])){
            return ['code'=>1001,'msg'=>'请选择行为类型'];
        }
        
        $data['bh_time'] = time();
        
        if(!empty($data['bh_id'])){
            $where=[];
            $where['bh_id'] = ['eq',$data['bh_id']];
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

    public function addHistory($userId, $mid, $rid, $sid = 0, $nid = 0, $progress = 0)
    {
        $data = [
            'user_id' => $userId,
            'bh_type' => self::TYPE_HISTORY,
            'bh_mid' => $mid,
            'bh_rid' => $rid,
            'bh_sid' => $sid,
            'bh_nid' => $nid,
            'bh_progress' => $progress,
            'bh_time' => time()
        ];
        
        $where = [
            'user_id' => $userId,
            'bh_type' => self::TYPE_HISTORY,
            'bh_mid' => $mid,
            'bh_rid' => $rid
        ];
        
        $existing = $this->where($where)->find();
        if($existing){
            $data['bh_id'] = $existing['bh_id'];
        }
        
        return $this->saveData($data);
    }

    public function toggleFavorite($userId, $mid, $rid)
    {
        $where = [
            'user_id' => $userId,
            'bh_type' => self::TYPE_FAVORITE,
            'bh_mid' => $mid,
            'bh_rid' => $rid
        ];
        
        $existing = $this->where($where)->find();
        if($existing){
            $this->delData(['bh_id' => $existing['bh_id']]);
            return ['code' => 1, 'msg' => '取消收藏成功', 'is_favorite' => false];
        } else {
            $data = [
                'user_id' => $userId,
                'bh_type' => self::TYPE_FAVORITE,
                'bh_mid' => $mid,
                'bh_rid' => $rid,
                'bh_time' => time()
            ];
            $this->saveData($data);
            return ['code' => 1, 'msg' => '收藏成功', 'is_favorite' => true];
        }
    }

    public function toggleFollow($userId, $mid, $rid)
    {
        $where = [
            'user_id' => $userId,
            'bh_type' => self::TYPE_FOLLOW,
            'bh_mid' => $mid,
            'bh_rid' => $rid
        ];
        
        $existing = $this->where($where)->find();
        if($existing){
            $this->delData(['bh_id' => $existing['bh_id']]);
            return ['code' => 1, 'msg' => '取消追剧成功', 'is_follow' => false];
        } else {
            $data = [
                'user_id' => $userId,
                'bh_type' => self::TYPE_FOLLOW,
                'bh_mid' => $mid,
                'bh_rid' => $rid,
                'bh_time' => time()
            ];
            $this->saveData($data);
            return ['code' => 1, 'msg' => '追剧成功', 'is_follow' => true];
        }
    }

    public function toggleLike($userId, $mid, $rid)
    {
        $where = [
            'user_id' => $userId,
            'bh_type' => self::TYPE_LIKE,
            'bh_mid' => $mid,
            'bh_rid' => $rid
        ];
        
        $existing = $this->where($where)->find();
        if($existing){
            $this->delData(['bh_id' => $existing['bh_id']]);
            return ['code' => 1, 'msg' => '取消点赞成功', 'is_like' => false];
        } else {
            $data = [
                'user_id' => $userId,
                'bh_type' => self::TYPE_LIKE,
                'bh_mid' => $mid,
                'bh_rid' => $rid,
                'bh_time' => time()
            ];
            $this->saveData($data);
            return ['code' => 1, 'msg' => '点赞成功', 'is_like' => true];
        }
    }

    public function getUserHistory($userId, $mid = 0, $limit = 20)
    {
        $where = [
            'user_id' => $userId,
            'bh_type' => self::TYPE_HISTORY
        ];
        if($mid > 0){
            $where['bh_mid'] = $mid;
        }
        $order = 'bh_time desc';
        return $this->listData($where, $order, 1, $limit, 0, '*', 0, 0);
    }

    public function getUserFavorites($userId, $mid = 0, $limit = 20)
    {
        $where = [
            'user_id' => $userId,
            'bh_type' => self::TYPE_FAVORITE
        ];
        if($mid > 0){
            $where['bh_mid'] = $mid;
        }
        $order = 'bh_time desc';
        return $this->listData($where, $order, 1, $limit, 0, '*', 0, 0);
    }

    public function getUserFollows($userId, $mid = 0, $limit = 20)
    {
        $where = [
            'user_id' => $userId,
            'bh_type' => self::TYPE_FOLLOW
        ];
        if($mid > 0){
            $where['bh_mid'] = $mid;
        }
        $order = 'bh_time desc';
        return $this->listData($where, $order, 1, $limit, 0, '*', 0, 0);
    }

    public function isFavorite($userId, $mid, $rid)
    {
        $where = [
            'user_id' => $userId,
            'bh_type' => self::TYPE_FAVORITE,
            'bh_mid' => $mid,
            'bh_rid' => $rid
        ];
        return $this->where($where)->count() > 0;
    }

    public function isFollow($userId, $mid, $rid)
    {
        $where = [
            'user_id' => $userId,
            'bh_type' => self::TYPE_FOLLOW,
            'bh_mid' => $mid,
            'bh_rid' => $rid
        ];
        return $this->where($where)->count() > 0;
    }

    public function isLike($userId, $mid, $rid)
    {
        $where = [
            'user_id' => $userId,
            'bh_type' => self::TYPE_LIKE,
            'bh_mid' => $mid,
            'bh_rid' => $rid
        ];
        return $this->where($where)->count() > 0;
    }
}
