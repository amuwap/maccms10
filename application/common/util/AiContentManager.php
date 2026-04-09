<?php
/**
 * AI内容管理服务
 * 作者：阿木
 * 网址：Amu5.Com
 * QQ：46552292
 * 功能：管理AI内容生成，包括自定义开关、提示词、参数，以及判断是否已生成过内容
 */
namespace app\common\util;

use app\common\model\Vod;
use app\common\model\Actor;
use app\common\model\Art;
use app\common\model\AiConfig;
use app\common\model\AiTask;
use app\common\model\AiGenerateConfig;
use app\common\model\AiGenerateStatus;
use app\common\model\AiGenerateHistory;
use think\Log;
use think\Cache;

class AiContentManager
{
    // 配置信息
    protected $config;
    // AI服务实例
    protected $aiService;
    // AI生成配置实例
    protected $aiGenerateConfig;
    // AI生成状态实例
    protected $aiGenerateStatus;
    // AI生成历史实例
    protected $aiGenerateHistory;
    // 缓存前缀
    protected $cachePrefix = 'ai_content_';
    // 生成类型映射
    protected $generateTypes = [
        'vod' => [
            'blurb' => 'vod_blurb',
            'content' => 'vod_content',
            'tags' => 'vod_tags',
            'score' => 'vod_score',
            'episodes' => 'vod_episode',
            'reviews' => 'vod_review',
            'comments' => 'vod_comment'
        ],
        'actor' => [
            'bio' => 'actor_content',
            'info' => 'actor_info'
        ],
        'art' => [
            'content' => 'art_content',
            'summary' => 'art_summary'
        ]
    ];
    
    /**
     * 构造函数
     * @param array $config AI配置信息，如果为null则自动获取激活的配置
     */
    public function __construct($config = null)
    {
        if ($config) {
            $this->config = $config;
        } else {
            $this->config = model('AiConfig')->getActiveConfig();
        }
        
        if ($this->config) {
            $this->aiService = new AiService($this->config);
        }
        
        // 初始化AI生成配置模型
        $this->aiGenerateConfig = model('AiGenerateConfig');
        // 初始化AI生成状态模型
        $this->aiGenerateStatus = model('AiGenerateStatus');
        // 初始化AI生成历史模型
        $this->aiGenerateHistory = model('AiGenerateHistory');
    }
    
    /**
     * 生成内容并保存到对应字段
     * @param string $type 内容类型 (vod, actor, art)
     * @param int $id 内容ID
     * @param array $generateFields 要生成的字段列表
     * @param array $options 生成选项
     * @return array 生成结果
     */
    public function generateAndSave($type, $id, $generateFields = [], $options = [])
    {
        if (!$this->aiService) {
            return ['code' => 1001, 'msg' => 'AI配置不可用'];
        }
        
        // 检查是否启用了AI生成
        if (!$this->isEnabled($type, $options)) {
            return ['code' => 1002, 'msg' => 'AI生成功能未启用'];
        }
        
        $results = [];
        $model = $this->getModel($type);
        if (!$model) {
            return ['code' => 1003, 'msg' => '不支持的内容类型'];
        }
        
        $item = $model->get($id);
        if (!$item) {
            return ['code' => 1004, 'msg' => $type . '不存在'];
        }
        
        $itemData = $item->toArray();
        
        foreach ($generateFields as $field) {
            // 检查是否启用了AI生成
            if (!$this->isEnabled($type, $field, $options)) {
                $results[$field] = ['code' => 1, 'msg' => '跳过生成，功能未启用'];
                continue;
            }
            
            // 检查是否需要生成
            if (!$this->shouldGenerate($type, $id, $field, $options)) {
                $results[$field] = ['code' => 1, 'msg' => '跳过生成，已存在内容或已生成过'];
                continue;
            }
            
            // 生成内容
            $generateResult = $this->generateContent($type, $itemData, $field, $options);
            if ($generateResult['code'] == 1) {
                // 保存到数据库
                $saveResult = $this->saveContent($type, $id, $field, $generateResult['data']);
                if ($saveResult['code'] == 1) {
                    // 标记为已生成
                    $this->markAsGenerated($type, $id, $field, $generateResult['data']);
                    
                    // 记录生成历史
                    $this->aiGenerateHistory->addHistory($type, $id, $field, $generateResult['data'], $options);
                    
                    $results[$field] = ['code' => 1, 'msg' => '生成并保存成功', 'data' => $generateResult['data']];
                } else {
                    $results[$field] = $saveResult;
                }
            } else {
                $results[$field] = $generateResult;
            }
        }
        
        return ['code' => 1, 'msg' => '生成完成', 'results' => $results];
    }
    
    /**
     * 检查AI生成功能是否启用
     * @param string $type 内容类型
     * @param string $field 字段名
     * @param array $options 选项
     * @return bool 是否启用
     */
    protected function isEnabled($type, $field, $options = [])
    {
        // 检查全局开关
        if (isset($options['enabled']) && !$options['enabled']) {
            return false;
        }
        
        // 检查配置中的开关
        if (isset($this->config['config_status']) && $this->config['config_status'] != 1) {
            return false;
        }
        
        // 检查特定类型和字段的开关
        $generateConfig = $this->aiGenerateConfig->getConfig($type, $field);
        if (!empty($generateConfig) && isset($generateConfig['status']) && $generateConfig['status'] != 1) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 检查是否需要生成内容
     * @param string $type 内容类型
     * @param int $id 内容ID
     * @param string $field 字段名
     * @param array $options 选项
     * @return bool 是否需要生成
     */
    protected function shouldGenerate($type, $id, $field, $options = [])
    {
        // 检查是否强制生成
        if (isset($options['force_generate']) && $options['force_generate']) {
            return true;
        }
        
        // 检查是否已经生成过
        if ($this->hasGenerated($type, $id, $field)) {
            return false;
        }
        
        // 检查对应字段是否为空
        $model = $this->getModel($type);
        if ($model) {
            $item = $model->get($id);
            if ($item) {
                $dbField = $this->getDbField($type, $field);
                if ($dbField) {
                    $value = $item[$dbField];
                    // 如果字段有内容且不是默认值，则跳过
                    if (!empty($value) && trim($value) != '' && trim($value) != '暂无' && trim($value) != '暂无介绍') {
                        return false;
                    }
                }
            }
        }
        
        return true;
    }
    
    /**
     * 检查是否已经生成过内容
     * @param string $type 内容类型
     * @param int $id 内容ID
     * @param string $field 字段名
     * @return bool 是否已生成
     */
    protected function hasGenerated($type, $id, $field)
    {
        // 先检查缓存
        $cacheKey = $this->getGeneratedCacheKey($type, $id, $field);
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return true;
        }
        
        // 再检查数据库
        $status = $this->aiGenerateStatus->hasGenerated($type, $id, $field);
        if ($status) {
            // 更新缓存
            Cache::set($cacheKey, 1, 86400 * 30);
        }
        return $status;
    }
    
    /**
     * 标记为已生成
     * @param string $type 内容类型
     * @param int $id 内容ID
     * @param string $field 字段名
     * @param string $content 生成的内容
     */
    protected function markAsGenerated($type, $id, $field, $content = '')
    {
        // 更新数据库状态
        $this->aiGenerateStatus->markAsGenerated($type, $id, $field, $content);
        
        // 更新缓存
        $cacheKey = $this->getGeneratedCacheKey($type, $id, $field);
        // 缓存30天
        Cache::set($cacheKey, 1, 86400 * 30);
    }
    
    /**
     * 获取生成标记的缓存键
     * @param string $type 内容类型
     * @param int $id 内容ID
     * @param string $field 字段名
     * @return string 缓存键
     */
    protected function getGeneratedCacheKey($type, $id, $field)
    {
        return $this->cachePrefix . 'generated_' . $type . '_' . $id . '_' . $field;
    }
    
    /**
     * 获取对应的数据模型
     * @param string $type 内容类型
     * @return mixed 模型实例
     */
    protected function getModel($type)
    {
        switch ($type) {
            case 'vod':
                return model('Vod');
            case 'actor':
                return model('Actor');
            case 'art':
                return model('Art');
            default:
                return null;
        }
    }
    
    /**
     * 获取数据库字段名
     * @param string $type 内容类型
     * @param string $field 字段名
     * @return string 数据库字段名
     */
    protected function getDbField($type, $field)
    {
        return isset($this->generateTypes[$type][$field]) ? $this->generateTypes[$type][$field] : $field;
    }
    
    /**
     * 生成内容
     * @param string $type 内容类型
     * @param array $itemData 内容数据
     * @param string $field 字段名
     * @param array $options 选项
     * @return array 生成结果
     */
    protected function generateContent($type, $itemData, $field, $options = [])
    {
        // 获取配置
        $generateConfig = $this->aiGenerateConfig->getConfig($type, $field);
        
        // 合并配置到选项中
        if (!empty($generateConfig['config'])) {
            $options = array_merge($generateConfig['config'], $options);
        }
        
        // 提取自定义参数
        $customParams = [
            'model' => $options['model'] ?? $this->config['config_model'] ?? 'gpt-4o-mini',
            'temperature' => $options['temperature'] ?? $this->config['config_temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? $this->config['config_max_tokens'] ?? 2000,
            'top_p' => $options['top_p'] ?? 0.9,
            'frequency_penalty' => $options['frequency_penalty'] ?? 0,
            'presence_penalty' => $options['presence_penalty'] ?? 0
        ];
        
        // 将自定义参数添加到选项中
        $options['model_params'] = $customParams;
        
        switch ($type) {
            case 'vod':
                return $this->generateVodContent($itemData, $field, $options);
            case 'actor':
                return $this->generateActorContent($itemData, $field, $options);
            case 'art':
                return $this->generateArtContent($itemData, $field, $options);
            default:
                return ['code' => 1005, 'msg' => '不支持的内容类型'];
        }
    }
    
    /**
     * 生成影视内容
     * @param array $vodData 影视数据
     * @param string $field 字段名
     * @param array $options 选项
     * @return array 生成结果
     */
    protected function generateVodContent($vodData, $field, $options = [])
    {
        $vodName = $vodData['vod_name'] ?? '';
        $typeId = $vodData['type_id'] ?? 0;
        
        switch ($field) {
            case 'blurb':
                $result = $this->aiService->generateIntro($vodName, $typeId, $options);
                break;
            case 'content':
                $result = $this->aiService->generateIntro($vodName, $typeId, $options);
                break;
            case 'tags':
                $localOptions = $options;
                $localOptions['type_name'] = isset($vodData['type']['type_name']) ? $vodData['type']['type_name'] : '';
                $localOptions['vod_content'] = $vodData['vod_content'] ?? '';
                $result = $this->aiService->generateTags($vodName, $localOptions);
                if ($result['code'] == 1 && isset($result['tags'])) {
                    $result['data'] = implode(',', $result['tags']);
                }
                break;
            case 'score':
                $localOptions = $options;
                $localOptions['type_name'] = isset($vodData['type']['type_name']) ? $vodData['type']['type_name'] : '';
                $localOptions['vod_actor'] = $vodData['vod_actor'] ?? '';
                $localOptions['vod_director'] = $vodData['vod_director'] ?? '';
                $result = $this->aiService->generateScore($vodName, $localOptions);
                if ($result['code'] == 1 && isset($result['score']['total'])) {
                    $result['data'] = $result['score']['total'];
                }
                break;
            case 'episodes':
                $localOptions = $options;
                $totalEpisodes = isset($vodData['vod_total']) ? intval($vodData['vod_total']) : 10;
                $result = $this->aiService->generateEpisodes($vodName, $totalEpisodes, $localOptions);
                if ($result['code'] == 1 && isset($result['episodes'])) {
                    $result['data'] = $result['episodes'];
                }
                break;
            case 'reviews':
                $localOptions = $options;
                $localOptions['type_name'] = isset($vodData['type']['type_name']) ? $vodData['type']['type_name'] : '';
                $result = $this->aiService->generateReviews($vodName, 2, $localOptions);
                if ($result['code'] == 1 && isset($result['reviews'])) {
                    $result['data'] = $result['reviews'];
                }
                break;
            case 'comments':
                $localOptions = $options;
                $localOptions['type_name'] = isset($vodData['type']['type_name']) ? $vodData['type']['type_name'] : '';
                $localOptions['vod_content'] = $vodData['vod_content'] ?? '';
                $result = $this->aiService->generateComments($vodName, 5, $localOptions);
                if ($result['code'] == 1 && isset($result['comments'])) {
                    $result['data'] = $result['comments'];
                }
                break;
            default:
                return ['code' => 1006, 'msg' => '不支持的影视字段'];
        }
        
        return $result;
    }
    
    /**
     * 生成演员内容
     * @param array $actorData 演员数据
     * @param string $field 字段名
     * @param array $options 选项
     * @return array 生成结果
     */
    protected function generateActorContent($actorData, $field, $options = [])
    {
        $actorName = $actorData['actor_name'] ?? '';
        $vodName = isset($options['vod_name']) ? $options['vod_name'] : '';
        
        switch ($field) {
            case 'bio':
            case 'info':
                $result = $this->aiService->generateActors($vodName, $actorName, $options);
                if ($result['code'] == 1 && isset($result['actors'][0])) {
                    $actorInfo = $result['actors'][0];
                    $result['data'] = $field == 'bio' ? $actorInfo['bio'] : json_encode($actorInfo);
                }
                break;
            default:
                return ['code' => 1007, 'msg' => '不支持的演员字段'];
        }
        
        return $result;
    }
    
    /**
     * 生成文章内容
     * @param array $artData 文章数据
     * @param string $field 字段名
     * @param array $options 选项
     * @return array 生成结果
     */
    protected function generateArtContent($artData, $field, $options = [])
    {
        $artTitle = $artData['art_name'] ?? '';
        
        switch ($field) {
            case 'content':
                // 构建文章内容提示词
                $prompt = isset($options['prompts']['art_content']) ? $options['prompts']['art_content'] : $this->buildArtContentPrompt($artTitle);
                $result = $this->aiService->generateContent($prompt, 'art_content', $options);
                break;
            default:
                return ['code' => 1008, 'msg' => '不支持的文章字段'];
        }
        
        return $result;
    }
    
    /**
     * 构建文章内容提示词
     * @param string $artTitle 文章标题
     * @return string 提示词
     */
    protected function buildArtContentPrompt($artTitle)
    {
        $prompt = "请为文章《{$artTitle}》生成详细的内容，800-1500字。";
        $prompt .= "\n\n要求：";
        $prompt .= "\n1. 内容丰富详细，结构清晰，逻辑严谨";
        $prompt .= "\n2. 语言流畅，表达准确，符合文章主题";
        $prompt .= "\n3. 包含必要的背景信息、分析和见解";
        $prompt .= "\n4. 内容要有层次感和逻辑性";
        $prompt .= "\n5. 避免使用过于专业的术语，让普通读者也能理解";
        $prompt .= "\n6. 可以适当引用相关案例或数据支持观点";
        $prompt .= "\n\n请直接返回文章内容，不要添加其他说明。";
        return $prompt;
    }
    
    /**
     * 保存内容到数据库
     * @param string $type 内容类型
     * @param int $id 内容ID
     * @param string $field 字段名
     * @param mixed $content 内容
     * @return array 保存结果
     */
    protected function saveContent($type, $id, $field, $content)
    {
        $model = $this->getModel($type);
        if (!$model) {
            return ['code' => 1009, 'msg' => '不支持的内容类型'];
        }
        
        $dbField = $this->getDbField($type, $field);
        if (!$dbField) {
            return ['code' => 1010, 'msg' => '不支持的字段'];
        }
        
        try {
            // 准备保存数据
            $data = [];
            
            // 根据字段类型处理内容
            switch ($field) {
                case 'episodes':
                case 'reviews':
                case 'comments':
                    // 这些字段可能需要特殊处理，例如保存为JSON或关联表
                    $data[$dbField] = is_array($content) ? json_encode($content, JSON_UNESCAPED_UNICODE) : $content;
                    break;
                case 'tags':
                    // 标签字段确保为逗号分隔的字符串
                    $data[$dbField] = is_array($content) ? implode(',', $content) : $content;
                    break;
                case 'score':
                    // 评分字段确保为数字
                    $data[$dbField] = is_numeric($content) ? floatval($content) : $content;
                    break;
                default:
                    $data[$dbField] = $content;
            }
            
            // 记录保存前的状态
            Log::debug('AI生成内容保存前: ' . json_encode([
                'type' => $type,
                'id' => $id,
                'field' => $field,
                'dbField' => $dbField,
                'content' => $content
            ]));
            
            // 执行更新
            $res = $model->allowField(true)->where(['id' => $id])->update($data);
            
            if ($res === false) {
                $error = $model->getError();
                Log::error('保存AI生成内容失败: ' . $error . ', 类型: ' . $type . ', ID: ' . $id . ', 字段: ' . $field);
                return ['code' => 1011, 'msg' => '保存失败：' . $error];
            }
            
            Log::debug('AI生成内容保存成功: ' . json_encode([
                'type' => $type,
                'id' => $id,
                'field' => $field,
                'affected_rows' => $res
            ]));
            
            return ['code' => 1, 'msg' => '保存成功'];
        } catch (\Exception $e) {
            Log::error('保存AI生成内容异常: ' . $e->getMessage() . ', 类型: ' . $type . ', ID: ' . $id . ', 字段: ' . $field);
            return ['code' => 1012, 'msg' => '保存失败：' . $e->getMessage()];
        }
    }
    
    /**
     * 批量生成内容
     * @param string $type 内容类型
     * @param array $ids 内容ID列表
     * @param array $generateFields 要生成的字段列表
     * @param array $options 生成选项
     * @return array 生成结果
     */
    public function batchGenerate($type, $ids, $generateFields = [], $options = [])
    {
        $results = [];
        $total = count($ids);
        $processed = 0;
        $successCount = 0;
        $errorCount = 0;
        
        // 批量获取未生成的内容ID，减少数据库查询
        $notGeneratedIds = [];
        foreach ($generateFields as $field) {
            $fieldNotGenerated = $this->aiGenerateStatus->getNotGeneratedIds($type, $ids, $field);
            $notGeneratedIds[$field] = $fieldNotGenerated;
        }
        
        // 处理每个ID
        foreach ($ids as $id) {
            $idResults = [];
            $idSuccess = false;
            
            // 处理每个字段
            foreach ($generateFields as $field) {
                // 检查是否需要生成
                if (!in_array($id, $notGeneratedIds[$field]) && !isset($options['force_generate'])) {
                    $idResults[$field] = ['code' => 1, 'msg' => '跳过生成，已存在内容或已生成过'];
                    continue;
                }
                
                // 生成内容
                $result = $this->generateAndSave($type, $id, [$field], $options);
                
                if (isset($result['results'][$field])) {
                    $idResults[$field] = $result['results'][$field];
                    if ($result['results'][$field]['code'] == 1) {
                        $idSuccess = true;
                    } else {
                        $errorCount++;
                    }
                } else {
                    $idResults[$field] = $result;
                    $errorCount++;
                }
            }
            
            $results[$id] = ['code' => 1, 'results' => $idResults];
            $processed++;
            if ($idSuccess) {
                $successCount++;
            }
            
            // 记录进度
            Log::debug('批量生成进度: ' . $processed . '/' . $total . ', 成功: ' . $successCount . ', 失败: ' . $errorCount);
        }
        
        return [
            'code' => 1, 
            'msg' => '批量生成完成', 
            'results' => $results,
            'stats' => [
                'total' => $total,
                'processed' => $processed,
                'success' => $successCount,
                'error' => $errorCount
            ]
        ];
    }
    
    /**
     * 获取生成配置
     * @param string $type 内容类型
     * @param string $field 字段名
     * @return array 配置
     */
    public function getGenerateConfig($type, $field)
    {
        $config = $this->aiGenerateConfig->getConfig($type, $field);
        if (!empty($config) && isset($config['config'])) {
            return $config['config'];
        }
        
        // 返回默认配置
        return [
            'enabled' => true,
            'prompt' => '',
            'model' => $this->config['config_model'] ?? 'gpt-4o-mini',
            'temperature' => $this->config['config_temperature'] ?? 0.7,
            'max_tokens' => $this->config['config_max_tokens'] ?? 2000,
            'force_generate' => false,
            'top_p' => 0.9,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        ];
    }
    
    /**
     * 设置生成配置
     * @param string $type 内容类型
     * @param string $field 字段名
     * @param array $config 配置
     * @return array 设置结果
     */
    public function setGenerateConfig($type, $field, $config = [])
    {
        try {
            $result = $this->aiGenerateConfig->saveConfig($type, $field, $config);
            if ($result) {
                return ['code' => 1, 'msg' => '配置保存成功'];
            } else {
                return ['code' => 0, 'msg' => '配置保存失败'];
            }
        } catch (Exception $e) {
            Log::error('保存AI生成配置失败: ' . $e->getMessage());
            return ['code' => 0, 'msg' => '配置保存失败: ' . $e->getMessage()];
        }
    }
    
    /**
     * 清除生成标记
     * @param string $type 内容类型
     * @param int $id 内容ID
     * @param string $field 字段名
     * @return array 清除结果
     */
    public function clearGeneratedMark($type, $id, $field = null)
    {
        try {
            // 清除数据库中的标记
            $this->aiGenerateStatus->clearGeneratedMark($type, $id, $field);
            
            // 清除缓存
            if ($field) {
                $cacheKey = $this->getGeneratedCacheKey($type, $id, $field);
                Cache::rm($cacheKey);
            } else {
                // 清除所有字段的生成标记
                foreach ($this->generateTypes[$type] as $f => $dbField) {
                    $cacheKey = $this->getGeneratedCacheKey($type, $id, $f);
                    Cache::rm($cacheKey);
                }
            }
            return ['code' => 1, 'msg' => '生成标记已清除'];
        } catch (\Exception $e) {
            Log::error('清除AI生成标记失败: ' . $e->getMessage());
            return ['code' => 0, 'msg' => '清除失败：' . $e->getMessage()];
        }
    }
    
    /**
     * 生成内容预览
     * @param string $type 内容类型
     * @param array $itemData 内容数据
     * @param string $field 字段名
     * @param array $options 选项
     * @return array 预览结果
     */
    public function generatePreview($type, $itemData, $field, $options = [])
    {
        if (!$this->aiService) {
            return ['code' => 1001, 'msg' => 'AI配置不可用'];
        }
        
        $result = $this->generateContent($type, $itemData, $field, $options);
        if ($result['code'] == 1) {
            return ['code' => 1, 'msg' => '预览生成成功', 'data' => $result['data']];
        }
        return $result;
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
        return $this->aiGenerateHistory->getHistoryList($where, $page, $limit);
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
        return $this->aiGenerateHistory->getHistoryByType($type, $field, $limit);
    }
    
    /**
     * 获取指定目标的生成历史
     * @param string $type 内容类型
     * @param int $targetId 目标ID
     * @return array 历史列表
     */
    public function getHistoryByTarget($type, $targetId)
    {
        return $this->aiGenerateHistory->getHistoryByTarget($type, $targetId);
    }
    
    /**
     * 删除生成历史
     * @param int $id 历史ID
     * @return array 删除结果
     */
    public function deleteHistory($id)
    {
        try {
            $result = $this->aiGenerateHistory->deleteHistory($id);
            if ($result) {
                return ['code' => 1, 'msg' => '历史记录删除成功'];
            } else {
                return ['code' => 0, 'msg' => '历史记录删除失败'];
            }
        } catch (\Exception $e) {
            Log::error('删除AI生成历史失败: ' . $e->getMessage());
            return ['code' => 0, 'msg' => '删除失败：' . $e->getMessage()];
        }
    }
    
    /**
     * 清空生成历史
     * @param array $where 查询条件
     * @return array 清空结果
     */
    public function clearHistory($where = [])
    {
        try {
            $result = $this->aiGenerateHistory->clearHistory($where);
            return ['code' => 1, 'msg' => '历史记录清空成功', 'deleted' => $result];
        } catch (\Exception $e) {
            Log::error('清空AI生成历史失败: ' . $e->getMessage());
            return ['code' => 0, 'msg' => '清空失败：' . $e->getMessage()];
        }
    }
    
    /**
     * 获取生成统计信息
     * @param array $where 查询条件
     * @return array 统计信息
     */
    public function getStatistics($where = [])
    {
        return $this->aiGenerateHistory->getStatistics($where);
    }
}
