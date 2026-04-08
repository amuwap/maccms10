<?php
namespace app\common\util;

use app\common\model\Vod;
use app\common\model\Comment;
use app\common\model\Tag;
use app\common\model\Plot;
use think\Log;

class AutoContentGenerator {
    protected $aiService;
    protected $batchSize = 5;
    protected $maxRetries = 3;

    public function __construct() {
        $this->aiService = new AiService();
    }

    /**
     * 自动为影视生成内容
     * @param int $vod_id 影视ID
     * @param array $types 要生成的内容类型
     * @param array $prompts 自定义提示词
     * @return array
     */
    public function generateContentForVod($vod_id, $types = ['intro', 'tags', 'comments'], $prompts = []) {
        try {
            // 获取影视信息
            $vod_info = model('Vod')->get($vod_id);
            if (!$vod_info) {
                return ['code' => 1001, 'msg' => '影视不存在'];
            }

            // 检查是否已经生成过内容
            if (!$this->needGenerate($vod_id, $types)) {
                return ['code' => 1002, 'msg' => '内容已存在，跳过生成'];
            }

            // 准备选项
            $options = [
                'vod_info' => $vod_info,
                'use_cache' => true,
                'cache_time' => 86400,
                'prompts' => $prompts
            ];

            // 批量生成内容
            $result = $this->aiService->batchGenerate([$vod_id], $types, $options);
            
            if ($result['code'] == 1) {
                $vod_result = $result['results'][$vod_id];
                if ($vod_result['code'] == 1) {
                    // 保存生成的内容
                    $save_result = $this->saveGeneratedContent($vod_id, $vod_result['data']);
                    if ($save_result['code'] == 1) {
                        return ['code' => 1, 'msg' => '内容生成并保存成功', 'data' => $save_result['data']];
                    } else {
                        return $save_result;
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('自动生成内容失败: ' . $e->getMessage());
            return ['code' => 1003, 'msg' => '生成失败: ' . $e->getMessage()];
        }
    }

    /**
     * 批量为多个影视生成内容
     * @param array $vod_ids 影视ID列表
     * @param array $types 要生成的内容类型
     * @param array $prompts 自定义提示词
     * @return array
     */
    public function batchGenerateContent($vod_ids, $types = ['intro', 'tags', 'comments'], $prompts = []) {
        $results = [];
        $batch_count = ceil(count($vod_ids) / $this->batchSize);

        for ($i = 0; $i < $batch_count; $i++) {
            $batch_ids = array_slice($vod_ids, $i * $this->batchSize, $this->batchSize);
            
            // 过滤需要生成内容的影视
            $need_generate_ids = [];
            foreach ($batch_ids as $vod_id) {
                if ($this->needGenerate($vod_id, $types)) {
                    $need_generate_ids[] = $vod_id;
                }
            }

            if (!empty($need_generate_ids)) {
                // 准备选项
                $options = [
                    'use_cache' => true,
                    'cache_time' => 86400,
                    'prompts' => $prompts
                ];
                
                // 批量生成内容
                $result = $this->aiService->batchGenerate($need_generate_ids, $types, $options);
                
                if ($result['code'] == 1) {
                    foreach ($result['results'] as $vod_id => $vod_result) {
                        if ($vod_result['code'] == 1) {
                            // 保存生成的内容
                            $save_result = $this->saveGeneratedContent($vod_id, $vod_result['data']);
                            $results[$vod_id] = $save_result;
                        } else {
                            $results[$vod_id] = $vod_result;
                        }
                    }
                }
            }

            // 避免API限流
            sleep(2);
        }

        return ['code' => 1, 'results' => $results];
    }

    /**
     * 检查是否需要生成内容
     * @param int $vod_id 影视ID
     * @param array $types 要生成的内容类型
     * @return bool
     */
    protected function needGenerate($vod_id, $types) {
        $vod_info = model('Vod')->get($vod_id);
        if (!$vod_info) {
            return false;
        }

        // 始终返回true，因为我们要替换原有内容
        return true;
    }

    /**
     * 保存生成的内容
     * @param int $vod_id 影视ID
     * @param array $data 生成的内容
     * @return array
     */
    protected function saveGeneratedContent($vod_id, $data) {
        $saved = [];

        try {
            // 保存简介
            if (isset($data['intro']) && $data['intro']['code'] == 1) {
                $content = $data['intro']['content'];
                model('Vod')->where(['vod_id' => $vod_id])->update(['vod_content' => $content]);
                $saved[] = '简介';
            }

            // 保存标签
            if (isset($data['tags']) && $data['tags']['code'] == 1) {
                // 先删除现有的标签关联
                model('TagRelation')->where(['tag_mid' => 1, 'tag_rid' => $vod_id])->delete();
                
                $tags = $data['tags']['tags'];
                foreach ($tags as $tag_name) {
                    $tag_info = model('Tag')->where(['tag_name' => $tag_name, 'tag_mid' => 1])->find();
                    if (!$tag_info) {
                        $tag_id = model('Tag')->insertGetId([
                            'tag_name' => $tag_name,
                            'tag_mid' => 1,
                            'tag_time' => time()
                        ]);
                    } else {
                        $tag_id = $tag_info['tag_id'];
                    }

                    // 关联标签
                    model('TagRelation')->insert([
                        'tag_id' => $tag_id,
                        'tag_rid' => $vod_id,
                        'tag_mid' => 1,
                        'tag_time' => time()
                    ]);
                }
                $saved[] = '标签';
            }

            // 保存评论
            if (isset($data['comments']) && $data['comments']['code'] == 1) {
                // 先删除现有的评论
                model('Comment')->where(['comment_mid' => 1, 'comment_rid' => $vod_id])->delete();
                
                $comments = $data['comments']['comments'];
                foreach ($comments as $comment) {
                    // 随机选择一个批量注册的用户
                    $user = model('User')->where(['user_reg_ip' => sprintf('%u', ip2long('127.0.0.1'))])->order('rand()')->find();
                    if ($user) {
                        model('Comment')->insert([
                            'comment_mid' => 1,
                            'comment_rid' => $vod_id,
                            'user_id' => $user['user_id'],
                            'comment_name' => $user['user_name'],
                            'comment_content' => $comment['content'],
                            'comment_time' => time(),
                            'comment_status' => 1,
                            'comment_ip' => sprintf('%u', ip2long('127.0.0.1'))
                        ]);
                    }
                }
                $saved[] = '评论';
            }

            return ['code' => 1, 'msg' => '内容保存成功', 'data' => $saved];
        } catch (\Exception $e) {
            Log::error('保存生成内容失败: ' . $e->getMessage());
            return ['code' => 1004, 'msg' => '保存失败: ' . $e->getMessage()];
        }
    }

    /**
     * 监听影视保存事件
     * @param array $vod_data 影视数据
     * @return array
     */
    public function onVodSave($vod_data) {
        $vod_id = isset($vod_data['vod_id']) ? $vod_data['vod_id'] : 0;
        if (!$vod_id) {
            return ['code' => 1001, 'msg' => '影视ID不存在'];
        }

        // 生成内容
        return $this->generateContentForVod($vod_id);
    }

    /**
     * 生成指定类型的内容
     * @param int $vod_id 影视ID
     * @param string $type 内容类型
     * @param array $prompts 自定义提示词
     * @return array
     */
    public function generateSpecificContent($vod_id, $type, $prompts = []) {
        return $this->generateContentForVod($vod_id, [$type], $prompts);
    }

    /**
     * 清理影视的AI生成内容
     * @param int $vod_id 影视ID
     * @return array
     */
    public function clearGeneratedContent($vod_id) {
        try {
            // 清理评论
            model('Comment')->where(['comment_mid' => 1, 'comment_rid' => $vod_id])->delete();

            // 清理标签关联
            model('TagRelation')->where(['tag_mid' => 1, 'tag_rid' => $vod_id])->delete();

            // 清理简介
            model('Vod')->where(['vod_id' => $vod_id])->update(['vod_content' => '']);

            return ['code' => 1, 'msg' => '内容清理成功'];
        } catch (\Exception $e) {
            Log::error('清理生成内容失败: ' . $e->getMessage());
            return ['code' => 1005, 'msg' => '清理失败: ' . $e->getMessage()];
        }
    }

    /**
     * 获取生成状态
     * @param int $vod_id 影视ID
     * @return array
     */
    public function getGenerateStatus($vod_id) {
        $vod_info = model('Vod')->get($vod_id);
        if (!$vod_info) {
            return ['code' => 1001, 'msg' => '影视不存在'];
        }

        $status = [
            'intro' => !empty($vod_info['vod_content']),
            'tags' => model('Tag')->where(['tag_mid' => 1, 'tag_rid' => $vod_id])->count() > 0,
            'comments' => model('Comment')->where(['comment_mid' => 1, 'comment_rid' => $vod_id])->count() > 0
        ];

        return ['code' => 1, 'data' => $status];
    }
}