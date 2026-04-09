<?php
/**
 * AI生成历史模型
 * 作者：阿木
 * 网址：Amu5.Com
 * QQ：46552292
 * 功能：记录和管理AI生成历史
 */
namespace app\common\model;

use think\Model;

class AiGenerateHistory extends Model
{
    // 表名
    protected $name = 'ai_generate_history';
    // 主键
    protected $pk = 'id';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    // 时间戳字段
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    /**
     * 添加生成历史
     * @param string $type 内容类型
     * @param int $targetId 目标ID
     * @param string $field 字段名
     * @param string $content 生成的内容
     * @param array $options 生成选项
     * @return bool 添加结果
     */
    public function addHistory($type, $targetId, $field, $content, $options = [])
    {
        $data = [
            'type' => $type,
            'target_id' => $targetId,
            'field' => $field,
            'content' => substr($content, 0, 1000), // 只存储内容摘要
            'options' => json_encode($options, JSON_UNESCAPED_UNICODE),
            'status' => 1,
            'create_time' => time(),
            'update_time' => time()
        ];
        
        return $this->insert($data);
    }
    
    /**
     * 获取生成历史列表
     * @param array $where 查询条件
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array 历史列表
     */
    public function getHistoryList($where = [], $page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        $total = $this->where($where)->count();
        $list = $this->where($where)
            ->order('create_time desc')
            ->limit($offset, $limit)
            ->select()
            ->toArray();
        
        foreach ($list as &$item) {
            if (!empty($item['options'])) {
                $item['options'] = json_decode($item['options'], true);
            }
        }
        
        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    }
    
    /**
     * 获取指定类型和字段的生成历史
     * @param string $type 内容类型
     * @param string $field 字段名
     * @param int $limit 限制数量
     * @return array 历史列表
     */
    public function getHistoryByType($type, $field, $limit = 10)
    {
        return $this->where([
            'type' => $type,
            'field' => $field
        ])->order('create_time desc')->limit($limit)->select()->toArray();
    }
    
    /**
     * 获取指定目标的生成历史
     * @param string $type 内容类型
     * @param int $targetId 目标ID
     * @return array 历史列表
     */
    public function getHistoryByTarget($type, $targetId)
    {
        return $this->where([
            'type' => $type,
            'target_id' => $targetId
        ])->order('create_time desc')->select()->toArray();
    }
    
    /**
     * 删除生成历史
     * @param int $id 历史ID
     * @return bool 删除结果
     */
    public function deleteHistory($id)
    {
        return $this->where(['id' => $id])->delete();
    }
    
    /**
     * 清空生成历史
     * @param array $where 查询条件
     * @return bool 清空结果
     */
    public function clearHistory($where = [])
    {
        return $this->where($where)->delete();
    }
    
    /**
     * 获取生成统计信息
     * @param array $where 查询条件
     * @return array 统计信息
     */
    public function getStatistics($where = [])
    {
        $total = $this->where($where)->count();
        $types = $this->where($where)->group('type')->column('type, count(*) as count');
        $fields = $this->where($where)->group('field')->column('field, count(*) as count');
        
        return [
            'total' => $total,
            'types' => $types,
            'fields' => $fields
        ];
    }
}
