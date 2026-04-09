<?php
/**
 * AI生成配置模型
 * 作者：阿木
 * 网址：Amu5.Com
 * QQ：46552292
 * 功能：存储和管理AI生成的配置信息
 */
namespace app\common\model;

use think\Model;

class AiGenerateConfig extends Model
{
    // 表名
    protected $name = 'ai_generate_config';
    // 主键
    protected $pk = 'id';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    // 时间戳字段
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    /**
     * 获取指定类型和字段的配置
     * @param string $type 内容类型
     * @param string $field 字段名
     * @return array 配置信息
     */
    public function getConfig($type, $field)
    {
        $config = $this->where(['type' => $type, 'field' => $field])->find();
        if ($config) {
            $data = $config->toArray();
            // 解析JSON配置
            if (!empty($data['config'])) {
                $data['config'] = json_decode($data['config'], true);
            }
            return $data;
        }
        return [];
    }
    
    /**
     * 保存配置
     * @param string $type 内容类型
     * @param string $field 字段名
     * @param array $config 配置信息
     * @return bool 保存结果
     */
    public function saveConfig($type, $field, $config)
    {
        $data = [
            'type' => $type,
            'field' => $field,
            'config' => json_encode($config, JSON_UNESCAPED_UNICODE),
            'status' => isset($config['enabled']) ? (int)$config['enabled'] : 1,
            'update_time' => time()
        ];
        
        $exists = $this->where(['type' => $type, 'field' => $field])->find();
        if ($exists) {
            return $this->where(['type' => $type, 'field' => $field])->update($data);
        } else {
            $data['create_time'] = time();
            return $this->insert($data);
        }
    }
    
    /**
     * 获取所有配置
     * @return array 配置列表
     */
    public function getAllConfig()
    {
        $configs = $this->select();
        $result = [];
        foreach ($configs as $config) {
            $data = $config->toArray();
            if (!empty($data['config'])) {
                $data['config'] = json_decode($data['config'], true);
            }
            $result[] = $data;
        }
        return $result;
    }
    
    /**
     * 删除配置
     * @param string $type 内容类型
     * @param string $field 字段名
     * @return bool 删除结果
     */
    public function deleteConfig($type, $field)
    {
        return $this->where(['type' => $type, 'field' => $field])->delete();
    }
    
    /**
     * 获取启用的配置列表
     * @return array 启用的配置列表
     */
    public function getEnabledConfig()
    {
        $configs = $this->where(['status' => 1])->select();
        $result = [];
        foreach ($configs as $config) {
            $data = $config->toArray();
            if (!empty($data['config'])) {
                $data['config'] = json_decode($data['config'], true);
            }
            $result[] = $data;
        }
        return $result;
    }
}
