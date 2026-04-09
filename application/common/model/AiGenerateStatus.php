<?php
/**
 * AI生成状态模型
 * 作者：阿木
 * 网址：Amu5.Com
 * QQ：46552292
 * 功能：跟踪和管理AI生成状态，确保已生成的内容跳过，未生成的内容生成
 */
namespace app\common\model;

use think\Model;

class AiGenerateStatus extends Model
{
    // 表名
    protected $name = 'ai_generate_status';
    // 主键
    protected $pk = 'id';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    // 时间戳字段
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    /**
     * 检查是否已生成
     * @param string $type 内容类型
     * @param int $id 内容ID
     * @param string $field 字段名
     * @return bool 是否已生成
     */
    public function hasGenerated($type, $id, $field)
    {
        $status = $this->where([
            'type' => $type,
            'target_id' => $id,
            'field' => $field
        ])->find();
        return $status ? true : false;
    }
    
    /**
     * 标记为已生成
     * @param string $type 内容类型
     * @param int $id 内容ID
     * @param string $field 字段名
     * @param string $content 生成的内容
     * @return bool 标记结果
     */
    public function markAsGenerated($type, $id, $field, $content = '')
    {
        $data = [
            'type' => $type,
            'target_id' => $id,
            'field' => $field,
            'content' => substr($content, 0, 255), // 只存储内容摘要
            'update_time' => time()
        ];
        
        $exists = $this->where([
            'type' => $type,
            'target_id' => $id,
            'field' => $field
        ])->find();
        
        if ($exists) {
            return $this->where([
                'type' => $type,
                'target_id' => $id,
                'field' => $field
            ])->update($data);
        } else {
            $data['create_time'] = time();
            return $this->insert($data);
        }
    }
    
    /**
     * 清除生成标记
     * @param string $type 内容类型
     * @param int $id 内容ID
     * @param string $field 字段名
     * @return bool 清除结果
     */
    public function clearGeneratedMark($type, $id, $field = null)
    {
        $where = [
            'type' => $type,
            'target_id' => $id
        ];
        
        if ($field) {
            $where['field'] = $field;
        }
        
        return $this->where($where)->delete();
    }
    
    /**
     * 获取生成状态列表
     * @param array $where 查询条件
     * @param int $limit 限制数量
     * @return array 状态列表
     */
    public function getStatusList($where = [], $limit = 100)
    {
        return $this->where($where)->order('create_time desc')->limit($limit)->select()->toArray();
    }
    
    /**
     * 获取未生成的内容列表
     * @param string $type 内容类型
     * @param array $ids 内容ID列表
     * @param string $field 字段名
     * @return array 未生成的ID列表
     */
    public function getNotGeneratedIds($type, $ids, $field)
    {
        if (empty($ids)) {
            return [];
        }
        
        $generated = $this->where([
            'type' => $type,
            'field' => $field
        ])->where('target_id', 'in', $ids)->column('target_id');
        
        return array_diff($ids, $generated);
    }
}
