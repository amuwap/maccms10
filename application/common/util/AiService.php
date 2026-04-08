<?php
namespace app\common\util;

use think\Cache;
use think\Log;

class AiService
{
    protected $config;
    protected $cachePrefix = 'ai_service_';
    protected $maxRetryCount = 3;
    protected $retryDelay = 2;
    protected $isStreaming = false;
    protected $streamCallback = null;

    public function __construct($config = null)
    {
        if ($config) {
            $this->config = $config;
        } else {
            $this->config = model('AiConfig')->getActiveConfig();
        }
    }

    public function setStreaming($streaming, $callback = null)
    {
        $this->isStreaming = $streaming;
        $this->streamCallback = $callback;
    }

    public function generateContent($prompt, $type = 'blurb', $options = [])
    {
        if (!$this->config) {
            return ['code' => 1001, 'msg' => 'AI配置不可用'];
        }

        $cacheKey = $this->getCacheKey($prompt, $type);
        $useCache = isset($options['use_cache']) ? $options['use_cache'] : true;
        $isAsync = isset($options['async']) ? $options['async'] : false;
        
        if ($isAsync) {
            return $this->createAsyncTask($prompt, $type, $options);
        }
        
        if ($useCache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return ['code' => 1, 'msg' => '生成成功(缓存)', 'data' => $cached, 'cached' => true];
            }
        }

        try {
            $result = $this->callApiWithRetry($prompt, $options);
            if ($result['code'] == 1) {
                if ($useCache && !empty($result['data'])) {
                    $cacheTime = isset($options['cache_time']) ? $options['cache_time'] : 86400;
                    Cache::set($cacheKey, $result['data'], $cacheTime);
                }
                return ['code' => 1, 'msg' => '生成成功', 'data' => $result['data']];
            }
            return $result;
        } catch (\Exception $e) {
            Log::error('AI生成失败: ' . $e->getMessage());
            return ['code' => 1002, 'msg' => 'AI生成失败: ' . $e->getMessage()];
        }
    }

    protected function callApiWithRetry($prompt, $options = [])
    {
        $retryCount = isset($options['max_retries']) ? $options['max_retries'] : $this->maxRetryCount;
        $lastError = null;

        for ($i = 0; $i < $retryCount; $i++) {
            try {
                $result = $this->callApi($prompt, $options);
                if ($result['code'] == 1) {
                    return $result;
                }
                $lastError = $result;
                
                if ($i < $retryCount - 1) {
                    sleep($this->retryDelay * ($i + 1));
                }
            } catch (\Exception $e) {
                $lastError = ['code' => 1003, 'msg' => $e->getMessage()];
                if ($i < $retryCount - 1) {
                    sleep($this->retryDelay * ($i + 1));
                }
            }
        }

        return $lastError;
    }

    protected function callApi($prompt, $options = [])
    {
        $apiUrl = $this->config['config_api_url'];
        if (empty($apiUrl)) {
            $apiUrl = $this->getDefaultApiUrl($this->config['config_provider']);
        }

        $headers = $this->buildHeaders();
        $data = $this->buildRequestData($prompt, $options);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, isset($options['timeout']) ? $options['timeout'] : 120);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['code' => 1004, 'msg' => 'CURL错误: ' . $error];
        }

        if ($httpCode != 200) {
            return ['code' => 1005, 'msg' => 'API返回错误: HTTP ' . $httpCode . ' - ' . $response];
        }

        return $this->parseResponse($response);
    }

    protected function buildHeaders()
    {
        $headers = ['Content-Type: application/json'];
        
        switch ($this->config['config_provider']) {
            case 'claude':
                $headers[] = 'x-api-key: ' . $this->config['config_api_key'];
                $headers[] = 'anthropic-version: 2023-06-01';
                break;
            case 'ernie':
                $accessToken = $this->getBaiduAccessToken();
                if ($accessToken) {
                    $headers[] = 'Authorization: Bearer ' . $accessToken;
                }
                break;
            default:
                $headers[] = 'Authorization: Bearer ' . $this->config['config_api_key'];
        }
        
        return $headers;
    }

    protected function buildRequestData($prompt, $options = [])
    {
        $provider = $this->config['config_provider'];
        $systemPrompt = isset($options['system_prompt']) ? $options['system_prompt'] : $this->getDefaultSystemPrompt();

        switch ($provider) {
            case 'claude':
                return [
                    'model' => $this->config['config_model'],
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => intval($this->config['config_max_tokens']),
                    'temperature' => floatval($this->config['config_temperature'])
                ];
            
            case 'ernie':
                return [
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ]
                ];
            
            default:
                $messages = [];
                if ($systemPrompt) {
                    $messages[] = ['role' => 'system', 'content' => $systemPrompt];
                }
                $messages[] = ['role' => 'user', 'content' => $prompt];

                return [
                    'model' => $this->config['config_model'],
                    'messages' => $messages,
                    'temperature' => floatval($this->config['config_temperature']),
                    'max_tokens' => intval($this->config['config_max_tokens']),
                    'top_p' => isset($options['top_p']) ? floatval($options['top_p']) : 0.9
                ];
        }
    }

    protected function parseResponse($response)
    {
        $result = json_decode($response, true);
        $provider = $this->config['config_provider'];

        switch ($provider) {
            case 'claude':
                if (isset($result['content'][0]['text'])) {
                    return ['code' => 1, 'data' => trim($result['content'][0]['text'])];
                }
                break;
            
            case 'ernie':
                if (isset($result['result'])) {
                    return ['code' => 1, 'data' => trim($result['result'])];
                }
                break;
            
            default:
                if (isset($result['choices'][0]['message']['content'])) {
                    return ['code' => 1, 'data' => trim($result['choices'][0]['message']['content'])];
                }
        }

        return ['code' => 1006, 'msg' => 'API返回格式错误', 'data' => $result];
    }

    protected function getBaiduAccessToken()
    {
        $apiKey = $this->config['config_api_key'];
        $secretKey = $this->config['config_secret_key'] ?? '';
        
        if (empty($secretKey)) {
            return null;
        }

        $cacheKey = $this->cachePrefix . 'baidu_token_' . md5($apiKey);
        $token = Cache::get($cacheKey);
        
        if ($token) {
            return $token;
        }

        $url = 'https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials&client_id=' . $apiKey . '&client_secret=' . $secretKey;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        if (isset($result['access_token'])) {
            Cache::set($cacheKey, $result['access_token'], $result['expires_in'] - 300);
            return $result['access_token'];
        }

        return null;
    }

    protected function getDefaultApiUrl($provider)
    {
        $urls = [
            'openai' => 'https://api.openai.com/v1/chat/completions',
            'claude' => 'https://api.anthropic.com/v1/messages',
            'qwen' => 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions',
            'ernie' => 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/completions',
            'glm' => 'https://open.bigmodel.cn/api/paas/v4/chat/completions',
            'deepseek' => 'https://api.deepseek.com/v1/chat/completions',
            'moonshot' => 'https://api.moonshot.cn/v1/chat/completions',
            'doubao' => 'https://ark.cn-beijing.volces.com/api/v3/chat/completions'
        ];
        return isset($urls[$provider]) ? $urls[$provider] : $urls['openai'];
    }

    protected function getDefaultSystemPrompt()
    {
        return "你是一个专业的影视内容创作助手，擅长生成高质量的影视简介、演员信息、影评和分集剧情。请用中文回复，保持内容客观真实，语言流畅优美。";
    }

    protected function getCacheKey($prompt, $type)
    {
        return $this->cachePrefix . $type . '_' . md5($prompt . json_encode($this->config));
    }

    public function clearCache($type = null)
    {
        if ($type) {
            $pattern = $this->cachePrefix . $type . '_*';
        } else {
            $pattern = $this->cachePrefix . '*';
        }
        
        Cache::clear($pattern);
        return ['code' => 1, 'msg' => '缓存清除成功'];
    }

    public function buildVodBlurbPrompt($vodName, $typeName = '', $actor = '', $director = '', $year = '', $area = '')
    {
        $prompt = "请为影视作品《{$vodName}》生成一份专业、吸引人的简介，300-600字。";
        $prompt .= "\n\n要求：";
        $prompt .= "\n1. 突出作品的核心卖点和特色";
        $prompt .= "\n2. 语言生动有趣，吸引观众";
        $prompt .= "\n3. 不要剧透关键剧情";
        $prompt .= "\n4. 保持客观中立的评价";
        
        $infoParts = [];
        if ($typeName) $infoParts[] = "类型：{$typeName}";
        if ($year) $infoParts[] = "年份：{$year}";
        if ($area) $infoParts[] = "地区：{$area}";
        if ($actor) $infoParts[] = "主演：{$actor}";
        if ($director) $infoParts[] = "导演：{$director}";
        
        if (!empty($infoParts)) {
            $prompt .= "\n\n作品信息：\n" . implode("\n", $infoParts);
        }
        
        $prompt .= "\n\n请直接返回简介内容，不要添加其他说明或标题。";
        return $prompt;
    }

    public function buildActorInfoPrompt($actorName, $vodName = '')
    {
        $prompt = "请为演员{$actorName}生成详细的个人资料。";
        if ($vodName) {
            $prompt .= "\n该演员出演过影视作品《{$vodName}》。";
        }
        $prompt .= "\n\n请按以下格式返回JSON数据，确保JSON格式正确：
{
    \"name\": \"姓名\",
    \"en_name\": \"英文名\",
    \"birthday\": \"出生日期(格式：YYYY-MM-DD)\",
    \"birtharea\": \"出生地\",
    \"height\": \"身高(数字，单位cm)\",
    \"blood\": \"血型\",
    \"starsign\": \"星座\",
    \"school\": \"毕业院校\",
    \"works\": \"代表作品(多个用逗号分隔)\",
    \"content\": \"详细介绍(200-500字)\",
    \"awards\": \"获奖情况\",
    \"career\": \"演艺经历\"
}";
        return $prompt;
    }

    public function buildReviewPrompt($vodName, $typeName = '', $score = '')
    {
        $prompt = "请为影视作品《{$vodName}》写一篇专业、有深度的影评文章，800-1500字。";
        
        if ($typeName) {
            $prompt .= "\n类型：{$typeName}";
        }
        
        $prompt .= "\n\n要求：";
        $prompt .= "\n1. 从剧情、演技、导演手法、视听语言等多角度分析";
        $prompt .= "\n2. 有个人观点和见解，但保持客观";
        $prompt .= "\n3. 不要过度剧透核心剧情";
        $prompt .= "\n4. 结构清晰，逻辑严谨";
        $prompt .= "\n5. 语言优美，可读性强";
        
        if ($score) {
            $prompt .= "\n6. 参考评分：{$score}分";
        }
        
        $prompt .= "\n\n请直接返回影评内容，包含标题和正文。标题格式：《作品名》：你的标题";
        return $prompt;
    }

    public function buildPlotPrompt($vodName, $totalEpisodes = 1, $currentEpisode = 0)
    {
        $prompt = "请为影视作品《{$vodName}》生成分集剧情介绍。";
        
        if ($totalEpisodes > 1) {
            $prompt .= "\n共{$totalEpisodes}集。";
        }
        
        if ($currentEpisode > 0) {
            $prompt .= "\n请生成第{$currentEpisode}集的剧情。";
        } else {
            $prompt .= "\n请生成所有集数的剧情。";
        }
        
        $prompt .= "\n\n要求：";
        $prompt .= "\n1. 每集剧情150-400字";
        $prompt .= "\n2. 情节连贯，逻辑清晰";
        $prompt .= "\n3. 突出每集的关键情节和转折";
        $prompt .= "\n4. 语言简洁明了";
        
        $prompt .= "\n\n请按以下格式返回JSON数组：
[
    {
        \"name\": \"第1集标题\",
        \"detail\": \"第1集剧情详细内容\"
    },
    {
        \"name\": \"第2集标题\",
        \"detail\": \"第2集剧情详细内容\"
    }
]";
        return $prompt;
    }

    public function buildScorePrompt($vodName, $typeName = '', $actor = '', $director = '')
    {
        $prompt = "请为影视作品《{$vodName}》进行综合评分。";
        
        $infoParts = [];
        if ($typeName) $infoParts[] = "类型：{$typeName}";
        if ($actor) $infoParts[] = "主演：{$actor}";
        if ($director) $infoParts[] = "导演：{$director}";
        
        if (!empty($infoParts)) {
            $prompt .= "\n" . implode("\n", $infoParts);
        }
        
        $prompt .= "\n\n请从以下维度进行评分（每项1-10分），并给出总分和简要评价：
{
    \"plot\": 剧情评分,
    \"acting\": 演技评分,
    \"directing\": 导演评分,
    \"visual\": 画面评分,
    \"music\": 音乐评分,
    \"total\": 总分,
    \"comment\": \"简要评价(50-100字)\"
}";
        return $prompt;
    }

    public function buildRolePrompt($actorName, $vodName, $roleName = '')
    {
        $prompt = "请为演员{$actorName}在影视作品《{$vodName}》中饰演的角色生成详细资料。";
        if ($roleName) {
            $prompt .= "\n角色名：{$roleName}";
        }
        $prompt .= "\n\n请按以下格式返回JSON数据：
{
    \"name\": \"角色名\",
    \"en_name\": \"角色英文名\",
    \"actor\": \"演员名\",
    \"character\": \"人物性格\",
    \"background\": \"角色背景\",
    \"significance\": \"角色意义\",
    \"content\": \"角色详细介绍(200-400字)\"
}";
        return $prompt;
    }

    public function buildTagsPrompt($vodName, $typeName = '', $content = '')
    {
        $prompt = "请为影视作品《{$vodName}》生成合适的标签。";
        
        if ($typeName) $prompt .= "\n类型：{$typeName}";
        if ($content) $prompt .= "\n简介：{$content}";
        
        $prompt .= "\n\n要求：";
        $prompt .= "\n1. 生成5-15个标签";
        $prompt .= "\n2. 标签要准确反映作品特色";
        $prompt .= "\n3. 包含类型、主题、风格等维度";
        $prompt .= "\n4. 用逗号分隔";
        
        $prompt .= "\n\n请直接返回标签，用逗号分隔，不要其他内容。";
        return $prompt;
    }

    public function generateIntro($vod_name, $type_id, $options = [])
    {
        $type_info = model('Type')->get($type_id);
        $type_name = $type_info ? $type_info['type_name'] : '视频';
        
        $vod_info = isset($options['vod_info']) ? $options['vod_info'] : [];
        
        // 检查是否有自定义提示词
        if (isset($options['prompts']) && isset($options['prompts']['intro'])) {
            $prompt = $options['prompts']['intro'];
            // 替换占位符
            $prompt = str_replace('{vod_name}', $vod_name, $prompt);
            $prompt = str_replace('{type_name}', $type_name, $prompt);
            $prompt = str_replace('{vod_actor}', isset($vod_info['vod_actor']) ? $vod_info['vod_actor'] : '', $prompt);
            $prompt = str_replace('{vod_director}', isset($vod_info['vod_director']) ? $vod_info['vod_director'] : '', $prompt);
            $prompt = str_replace('{vod_year}', isset($vod_info['vod_year']) ? $vod_info['vod_year'] : '', $prompt);
            $prompt = str_replace('{vod_area}', isset($vod_info['vod_area']) ? $vod_info['vod_area'] : '', $prompt);
        } else {
            $prompt = $this->buildVodBlurbPrompt(
                $vod_name, 
                $type_name,
                isset($vod_info['vod_actor']) ? $vod_info['vod_actor'] : '',
                isset($vod_info['vod_director']) ? $vod_info['vod_director'] : '',
                isset($vod_info['vod_year']) ? $vod_info['vod_year'] : '',
                isset($vod_info['vod_area']) ? $vod_info['vod_area'] : ''
            );
        }
        
        $result = $this->generateContent($prompt, 'intro', $options);
        if ($result['code'] == 1) {
            return ['code' => 1, 'content' => $result['data']];
        }
        return $result;
    }

    public function generateActors($vod_name, $actor_names = '', $options = [])
    {
        $actors = explode(',', $actor_names);
        $actor_list = [];
        $errors = [];
        
        foreach ($actors as $actor_name) {
            $actor_name = trim($actor_name);
            if (!empty($actor_name)) {
                // 检查是否有自定义提示词
                if (isset($options['prompts']) && isset($options['prompts']['actors'])) {
                    $prompt = $options['prompts']['actors'];
                    // 替换占位符
                    $prompt = str_replace('{actor_name}', $actor_name, $prompt);
                    $prompt = str_replace('{vod_name}', $vod_name, $prompt);
                } else {
                    $prompt = $this->buildActorInfoPrompt($actor_name, $vod_name);
                }
                
                $result = $this->generateContent($prompt, 'actor', $options);
                if ($result['code'] == 1) {
                    $actor_data = json_decode($result['data'], true);
                    if (is_array($actor_data)) {
                        $actor_list[] = [
                            'name' => $actor_data['name'] ?? $actor_name,
                            'bio' => $actor_data['content'] ?? '',
                            'en_name' => $actor_data['en_name'] ?? '',
                            'birthday' => $actor_data['birthday'] ?? '',
                            'birtharea' => $actor_data['birtharea'] ?? '',
                            'height' => $actor_data['height'] ?? '',
                            'blood' => $actor_data['blood'] ?? '',
                            'starsign' => $actor_data['starsign'] ?? '',
                            'school' => $actor_data['school'] ?? '',
                            'works' => $actor_data['works'] ?? '',
                            'image' => ''
                        ];
                    }
                } else {
                    $errors[] = $actor_name . ': ' . $result['msg'];
                }
            }
        }
        
        if (!empty($actor_list)) {
            $return = ['code' => 1, 'actors' => $actor_list];
            if (!empty($errors)) {
                $return['warnings'] = $errors;
            }
            return $return;
        }
        return ['code' => 0, 'msg' => '未生成演员信息', 'errors' => $errors];
    }

    public function generateReviews($vod_name, $count = 3, $options = [])
    {
        $reviews = [];
        $type_name = isset($options['type_name']) ? $options['type_name'] : '';
        
        for ($i = 0; $i < $count; $i++) {
            // 检查是否有自定义提示词
            if (isset($options['prompts']) && isset($options['prompts']['reviews'])) {
                $prompt = $options['prompts']['reviews'];
                // 替换占位符
                $prompt = str_replace('{vod_name}', $vod_name, $prompt);
                $prompt = str_replace('{type_name}', $type_name, $prompt);
            } else {
                $prompt = $this->buildReviewPrompt($vod_name, $type_name);
            }
            
            $localOptions = $options;
            $localOptions['use_cache'] = false;
            
            $result = $this->generateContent($prompt, 'review', $localOptions);
            if ($result['code'] == 1) {
                $content = $result['data'];
                $title = "《{$vod_name}》影评" . ($i + 1);
                
                if (preg_match('/《.*?》[：:]\s*(.+)/', $content, $matches)) {
                    $title = $matches[0];
                    $content = trim(substr($content, strlen($matches[0])));
                }
                
                $reviews[] = [
                    'title' => $title,
                    'content' => $content
                ];
            }
        }
        
        if (!empty($reviews)) {
            return ['code' => 1, 'reviews' => $reviews];
        }
        return ['code' => 0, 'msg' => '未生成影评'];
    }

    public function generateEpisodes($vod_name, $total_episodes = 10, $options = [])
    {
        // 检查是否有自定义提示词
        if (isset($options['prompts']) && isset($options['prompts']['episodes'])) {
            $prompt = $options['prompts']['episodes'];
            // 替换占位符
            $prompt = str_replace('{vod_name}', $vod_name, $prompt);
            $prompt = str_replace('{total_episodes}', $total_episodes, $prompt);
        } else {
            $prompt = $this->buildPlotPrompt($vod_name, $total_episodes);
        }
        
        $result = $this->generateContent($prompt, 'plot', $options);
        
        if ($result['code'] == 1) {
            $episodes = json_decode($result['data'], true);
            if (is_array($episodes)) {
                $formatted_episodes = [];
                foreach ($episodes as $index => $episode) {
                    $formatted_episodes[] = [
                        'title' => $episode['name'] ?? '第' . ($index + 1) . '集',
                        'content' => $episode['detail'] ?? ''
                    ];
                }
                return ['code' => 1, 'episodes' => $formatted_episodes];
            }
        }
        return $result;
    }

    public function generateScore($vod_name, $options = [])
    {
        $type_name = isset($options['type_name']) ? $options['type_name'] : '';
        $actor = isset($options['vod_actor']) ? $options['vod_actor'] : '';
        $director = isset($options['vod_director']) ? $options['vod_director'] : '';
        
        // 检查是否有自定义提示词
        if (isset($options['prompts']) && isset($options['prompts']['score'])) {
            $prompt = $options['prompts']['score'];
            // 替换占位符
            $prompt = str_replace('{vod_name}', $vod_name, $prompt);
            $prompt = str_replace('{type_name}', $type_name, $prompt);
            $prompt = str_replace('{actor}', $actor, $prompt);
            $prompt = str_replace('{director}', $director, $prompt);
        } else {
            $prompt = $this->buildScorePrompt($vod_name, $type_name, $actor, $director);
        }
        
        $result = $this->generateContent($prompt, 'score', $options);
        
        if ($result['code'] == 1) {
            $score_data = json_decode($result['data'], true);
            if (is_array($score_data)) {
                return ['code' => 1, 'score' => $score_data];
            }
        }
        return $result;
    }

    public function generateTags($vod_name, $options = [])
    {
        $type_name = isset($options['type_name']) ? $options['type_name'] : '';
        $content = isset($options['vod_content']) ? $options['vod_content'] : '';
        
        // 检查是否有自定义提示词
        if (isset($options['prompts']) && isset($options['prompts']['tags'])) {
            $prompt = $options['prompts']['tags'];
            // 替换占位符
            $prompt = str_replace('{vod_name}', $vod_name, $prompt);
            $prompt = str_replace('{type_name}', $type_name, $prompt);
            $prompt = str_replace('{content}', $content, $prompt);
        } else {
            $prompt = $this->buildTagsPrompt($vod_name, $type_name, $content);
        }
        
        $result = $this->generateContent($prompt, 'tags', $options);
        
        if ($result['code'] == 1) {
            $tags = array_map('trim', explode(',', $result['data']));
            $tags = array_filter($tags);
            return ['code' => 1, 'tags' => $tags];
        }
        return $result;
    }

    /**
     * 生成AI评论
     * @param string $vod_name 影视名称
     * @param array $options 选项
     * @return array
     */
    public function generateComments($vod_name, $count = 5, $options = [])
    {
        $comments = [];
        $type_name = isset($options['type_name']) ? $options['type_name'] : '';
        $content = isset($options['vod_content']) ? $options['vod_content'] : '';
        
        for ($i = 0; $i < $count; $i++) {
            // 检查是否有自定义提示词
            if (isset($options['prompts']) && isset($options['prompts']['comments'])) {
                $prompt = $options['prompts']['comments'];
                // 替换占位符
                $prompt = str_replace('{vod_name}', $vod_name, $prompt);
                $prompt = str_replace('{type_name}', $type_name, $prompt);
                $prompt = str_replace('{content}', $content, $prompt);
            } else {
                $prompt = $this->buildCommentPrompt($vod_name, $type_name, $content);
            }
            
            $localOptions = $options;
            $localOptions['use_cache'] = false;
            
            $result = $this->generateContent($prompt, 'comment', $localOptions);
            if ($result['code'] == 1) {
                $comments[] = [
                    'content' => $result['data'],
                    'created_at' => time()
                ];
            }
        }
        
        if (!empty($comments)) {
            return ['code' => 1, 'comments' => $comments];
        }
        return ['code' => 0, 'msg' => '未生成评论'];
    }

    /**
     * 构建评论提示词
     * @param string $vod_name 影视名称
     * @param string $type_name 类型名称
     * @param string $content 影视内容
     * @return string
     */
    public function buildCommentPrompt($vod_name, $type_name = '', $content = '')
    {
        $prompt = "请为影视作品《{$vod_name}》生成一条真实、自然的用户评论。";
        
        if ($type_name) {
            $prompt .= "\n类型：{$type_name}";
        }
        
        if ($content) {
            $prompt .= "\n简介：{$content}";
        }
        
        $prompt .= "\n\n要求：";
        $prompt .= "\n1. 语言口语化，符合普通观众的评论风格";
        $prompt .= "\n2. 表达真实的观影感受";
        $prompt .= "\n3. 可以包含具体的情节或演员表现";
        $prompt .= "\n4. 长度50-200字";
        $prompt .= "\n5. 不要使用专业影评术语";
        $prompt .= "\n6. 避免剧透关键剧情";
        
        $prompt .= "\n\n请直接返回评论内容，不要添加其他说明。";
        return $prompt;
    }

    public function batchGenerate($vod_ids, $types = ['intro'], $options = [])
    {
        $results = [];
        
        foreach ($vod_ids as $vod_id) {
            $vod_info = model('Vod')->get($vod_id);
            if (!$vod_info) {
                $results[$vod_id] = ['code' => 0, 'msg' => '视频不存在'];
                continue;
            }
            
            $vod_results = [];
            $localOptions = $options;
            $localOptions['vod_info'] = $vod_info;
            
            foreach ($types as $type) {
                switch ($type) {
                    case 'intro':
                        $vod_results['intro'] = $this->generateIntro(
                            $vod_info['vod_name'], 
                            $vod_info['type_id'],
                            $localOptions
                        );
                        break;
                    case 'actors':
                        $vod_results['actors'] = $this->generateActors(
                            $vod_info['vod_name'],
                            $vod_info['vod_actor'],
                            $localOptions
                        );
                        break;
                    case 'reviews':
                        $vod_results['reviews'] = $this->generateReviews(
                            $vod_info['vod_name'],
                            2,
                            $localOptions
                        );
                        break;
                    case 'episodes':
                        $vod_results['episodes'] = $this->generateEpisodes(
                            $vod_info['vod_name'],
                            10,
                            $localOptions
                        );
                        break;
                    case 'score':
                        $localOptions['type_name'] = isset($vod_info['type']['type_name']) ? $vod_info['type']['type_name'] : '';
                        $vod_results['score'] = $this->generateScore(
                            $vod_info['vod_name'],
                            $localOptions
                        );
                        break;
                    case 'tags':
                        $localOptions['type_name'] = isset($vod_info['type']['type_name']) ? $vod_info['type']['type_name'] : '';
                        $localOptions['vod_content'] = $vod_info['vod_content'];
                        $vod_results['tags'] = $this->generateTags(
                            $vod_info['vod_name'],
                            $localOptions
                        );
                        break;
                    case 'comments':
                        $localOptions['type_name'] = isset($vod_info['type']['type_name']) ? $vod_info['type']['type_name'] : '';
                        $localOptions['vod_content'] = $vod_info['vod_content'];
                        $vod_results['comments'] = $this->generateComments(
                            $vod_info['vod_name'],
                            5,
                            $localOptions
                        );
                        break;
                }
            }
            
            $results[$vod_id] = ['code' => 1, 'data' => $vod_results];
        }
        
        return ['code' => 1, 'results' => $results];
    }

    protected function createAsyncTask($prompt, $type, $options = [])
    {
        try {
            $taskId = md5(uniqid() . time());
            $taskData = [
                'task_id' => $taskId,
                'task_type' => $type,
                'task_prompt' => $prompt,
                'task_options' => json_encode($options),
                'task_config' => json_encode($this->config),
                'task_status' => 'pending',
                'task_create_time' => time(),
                'task_update_time' => time()
            ];

            model('AiTask')->insert($taskData);
            $this->executeAsyncTask($taskId);

            return ['code' => 1, 'msg' => '异步任务已创建', 'task_id' => $taskId];
        } catch (\Exception $e) {
            Log::error('创建AI异步任务失败: ' . $e->getMessage());
            return ['code' => 1010, 'msg' => '创建异步任务失败: ' . $e->getMessage()];
        }
    }

    protected function executeAsyncTask($taskId)
    {
        try {
            $task = model('AiTask')->where(['task_id' => $taskId])->find();
            if (!$task) {
                return false;
            }

            $task->task_status = 'processing';
            $task->task_update_time = time();
            $task->save();

            $options = json_decode($task->task_options, true);
            $config = json_decode($task->task_config, true);

            $aiService = new self($config);
            $options['use_cache'] = false;
            $result = $aiService->generateContent($task->task_prompt, $task->task_type, $options);

            $task->task_status = $result['code'] == 1 ? 'completed' : 'failed';
            $task->task_result = isset($result['data']) ? json_encode($result['data']) : '';
            $task->task_error = isset($result['msg']) ? $result['msg'] : '';
            $task->task_update_time = time();
            $task->save();

            return true;
        } catch (\Exception $e) {
            Log::error('执行AI异步任务失败: ' . $e->getMessage());
            if (isset($task)) {
                $task->task_status = 'failed';
                $task->task_error = $e->getMessage();
                $task->task_update_time = time();
                $task->save();
            }
            return false;
        }
    }

    public function getAsyncTaskResult($taskId)
    {
        $task = model('AiTask')->where(['task_id' => $taskId])->find();
        if (!$task) {
            return ['code' => 0, 'msg' => '任务不存在'];
        }

        $result = [
            'code' => 1,
            'task_id' => $task['task_id'],
            'status' => $task['task_status'],
            'create_time' => date('Y-m-d H:i:s', $task['task_create_time']),
            'update_time' => date('Y-m-d H:i:s', $task['task_update_time'])
        ];

        if ($task['task_status'] == 'completed' && !empty($task['task_result'])) {
            $result['data'] = json_decode($task['task_result'], true);
        }

        if ($task['task_status'] == 'failed' && !empty($task['task_error'])) {
            $result['error'] = $task['task_error'];
        }

        return $result;
    }

    protected function callApiWithStream($prompt, $options = [])
    {
        $apiUrl = $this->config['config_api_url'];
        if (empty($apiUrl)) {
            $apiUrl = $this->getDefaultApiUrl($this->config['config_provider']);
        }

        $headers = $this->buildHeaders();
        $data = $this->buildRequestData($prompt, $options);
        $data['stream'] = true;

        $fullContent = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, isset($options['timeout']) ? $options['timeout'] : 300);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use (&$fullContent) {
            $lines = explode("\n", $data);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || $line === 'data: [DONE]') {
                    continue;
                }

                if (strpos($line, 'data: ') === 0) {
                    $jsonStr = substr($line, 6);
                    $json = json_decode($jsonStr, true);
                    if ($json) {
                        $content = $this->parseStreamChunk($json);
                        if ($content) {
                            $fullContent .= $content;
                            if ($this->streamCallback && is_callable($this->streamCallback)) {
                                call_user_func($this->streamCallback, $content);
                            }
                        }
                    }
                }
            }
            return strlen($data);
        });

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['code' => 1004, 'msg' => 'CURL错误: ' . $error];
        }

        if ($httpCode != 200) {
            return ['code' => 1005, 'msg' => 'API返回错误: HTTP ' . $httpCode];
        }

        return ['code' => 1, 'data' => trim($fullContent)];
    }

    protected function parseStreamChunk($json)
    {
        $provider = $this->config['config_provider'];

        switch ($provider) {
            case 'claude':
                if (isset($json['type']) && $json['type'] === 'content_block_delta') {
                    return $json['delta']['text'] ?? '';
                }
                break;

            case 'ernie':
                return $json['result'] ?? '';

            default:
                if (isset($json['choices'][0]['delta']['content'])) {
                    return $json['choices'][0]['delta']['content'];
                }
        }

        return '';
    }

    public function listAvailableModels()
    {
        $models = [
            'openai' => [
                'gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-4', 'gpt-3.5-turbo'
            ],
            'claude' => [
                'claude-3-5-sonnet-20241022', 'claude-3-opus-20240229', 'claude-3-sonnet-20240229', 'claude-3-haiku-20240307'
            ],
            'qwen' => [
                'qwen-plus', 'qwen-turbo', 'qwen-max', 'qwen-long'
            ],
            'ernie' => [
                'ernie-4.0', 'ernie-3.5', 'ernie-lite', 'ernie-speed'
            ],
            'glm' => [
                'glm-4', 'glm-4-flash', 'glm-3-turbo'
            ],
            'deepseek' => [
                'deepseek-chat', 'deepseek-coder'
            ],
            'moonshot' => [
                'moonshot-v1-8k', 'moonshot-v1-32k', 'moonshot-v1-128k'
            ],
            'doubao' => [
                'doubao-pro-1.5', 'doubao-pro', 'doubao-lite'
            ]
        ];

        return ['code' => 1, 'models' => $models];
    }

    public function testConnection($options = [])
    {
        if (!$this->config) {
            return ['code' => 0, 'msg' => 'AI配置不可用'];
        }

        $testPrompt = '请回复"连接成功"三个字，不要添加其他内容。';
        $result = $this->generateContent($testPrompt, 'test', ['use_cache' => false, 'cache_time' => 0]);

        if ($result['code'] == 1) {
            $success = (strpos($result['data'], '连接成功') !== false || trim($result['data']) == '连接成功');
            return [
                'code' => $success ? 1 : 0,
                'msg' => $success ? 'AI连接测试成功' : 'AI连接测试失败: 响应内容不正确',
                'response' => $result['data']
            ];
        }

        return $result;
    }
}
