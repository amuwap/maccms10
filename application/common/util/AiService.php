<?php
namespace app\common\util;

use think\Log;

class AiService
{
    protected $config;

    public function __construct($config = null)
    {
        if ($config) {
            $this->config = $config;
        } else {
            $this->config = model('AiConfig')->getActiveConfig();
        }
    }

    public function generateContent($prompt, $type = 'blurb')
    {
        if (!$this->config) {
            return ['code' => 1001, 'msg' => 'AI配置不可用'];
        }

        try {
            $result = $this->callApi($prompt);
            if ($result['code'] == 1) {
                return ['code' => 1, 'msg' => '生成成功', 'data' => $result['data']];
            }
            return $result;
        } catch (\Exception $e) {
            Log::error('AI生成失败: ' . $e->getMessage());
            return ['code' => 1002, 'msg' => 'AI生成失败: ' . $e->getMessage()];
        }
    }

    protected function callApi($prompt)
    {
        $apiUrl = $this->config['config_api_url'];
        if (empty($apiUrl)) {
            $apiUrl = $this->getDefaultApiUrl($this->config['config_provider']);
        }

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->config['config_api_key']
        ];

        $data = [
            'model' => $this->config['config_model'],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => floatval($this->config['config_temperature']),
            'max_tokens' => intval($this->config['config_max_tokens'])
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['code' => 1003, 'msg' => 'CURL错误: ' . $error];
        }

        if ($httpCode != 200) {
            return ['code' => 1004, 'msg' => 'API返回错误: HTTP ' . $httpCode . ' - ' . $response];
        }

        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            return ['code' => 1, 'data' => trim($result['choices'][0]['message']['content'])];
        }

        return ['code' => 1005, 'msg' => 'API返回格式错误', 'data' => $result];
    }

    protected function getDefaultApiUrl($provider)
    {
        $urls = [
            'openai' => 'https://api.openai.com/v1/chat/completions',
            'claude' => 'https://api.anthropic.com/v1/messages',
            'qwen' => 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions',
            'ernie' => 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/completions',
            'glm' => 'https://open.bigmodel.cn/api/paas/v4/chat/completions',
        ];
        return isset($urls[$provider]) ? $urls[$provider] : $urls['openai'];
    }

    public function buildVodBlurbPrompt($vodName, $typeName = '', $actor = '', $director = '')
    {
        $prompt = "请为影视作品《{$vodName}》生成一份详细的简介，200-500字。";
        if ($typeName) {
            $prompt .= "\n类型：{$typeName}";
        }
        if ($actor) {
            $prompt .= "\n主演：{$actor}";
        }
        if ($director) {
            $prompt .= "\n导演：{$director}";
        }
        $prompt .= "\n请直接返回简介内容，不要添加其他说明。";
        return $prompt;
    }

    public function buildActorInfoPrompt($actorName, $vodName = '')
    {
        $prompt = "请为演员{$actorName}生成详细资料。";
        if ($vodName) {
            $prompt .= "\n该演员出演过影视作品《{$vodName}》。";
        }
        $prompt .= "\n请按以下格式返回JSON数据：
{
    \"name\": \"姓名\",
    \"en_name\": \"英文名\",
    \"birthday\": \"出生日期\",
    \"birtharea\": \"出生地\",
    \"height\": \"身高\",
    \"blood\": \"血型\",
    \"starsign\": \"星座\",
    \"school\": \"毕业院校\",
    \"works\": \"代表作品\",
    \"content\": \"详细介绍\"
}";
        return $prompt;
    }

    public function buildReviewPrompt($vodName, $typeName = '')
    {
        $prompt = "请为影视作品《{$vodName}》写一篇影评文章，500-1000字。";
        if ($typeName) {
            $prompt .= "\n类型：{$typeName}";
        }
        $prompt .= "\n要求客观真实，有个人观点，不要剧透太多。请直接返回影评内容。";
        return $prompt;
    }

    public function buildPlotPrompt($vodName, $totalEpisodes = 1)
    {
        $prompt = "请为影视作品《{$vodName}》生成分集剧情介绍。";
        if ($totalEpisodes > 1) {
            $prompt .= "\n共{$totalEpisodes}集。";
        }
        $prompt .= "\n请按以下格式返回JSON数组：
[
    {
        \"name\": \"第1集标题\",
        \"detail\": \"第1集剧情详细内容\"
    },
    {
        \"name\": \"第2集标题\",
        \"detail\": \"第2集剧情详细内容\"
    }
]
每集剧情详细内容100-300字。";
        return $prompt;
    }

    public function buildScorePrompt($vodName, $typeName = '')
    {
        $prompt = "请为影视作品《{$vodName}》评分。";
        if ($typeName) {
            $prompt .= "\n类型：{$typeName}";
        }
        $prompt .= "\n请只返回一个1-10之间的数字，表示评分（10分制）。";
        return $prompt;
    }

    public function buildRolePrompt($actorName, $vodName, $roleName = '')
    {
        $prompt = "请为演员{$actorName}在影视作品《{$vodName}》中饰演的角色生成资料。";
        if ($roleName) {
            $prompt .= "\n角色名：{$roleName}";
        }
        $prompt .= "\n请按以下格式返回JSON数据：
{
    \"name\": \"角色名\",
    \"en_name\": \"角色英文名\",
    \"actor\": \"演员名\",
    \"content\": \"角色详细介绍\"
}";
        return $prompt;
    }

    public function generateIntro($vod_name, $type_id)
    {
        $type_info = model('Type')->get($type_id);
        $type_name = $type_info ? $type_info['type_name'] : '视频';
        
        $prompt = $this->buildVodBlurbPrompt($vod_name, $type_name);
        $result = $this->generateContent($prompt, 'intro');
        if ($result['code'] == 1) {
            return ['code' => 1, 'content' => $result['data']];
        }
        return $result;
    }

    public function generateActors($vod_name, $actor_names = '')
    {
        $actors = explode(',', $actor_names);
        $actor_list = [];
        
        foreach ($actors as $actor_name) {
            $actor_name = trim($actor_name);
            if (!empty($actor_name)) {
                $prompt = $this->buildActorInfoPrompt($actor_name, $vod_name);
                $result = $this->generateContent($prompt, 'actor');
                if ($result['code'] == 1) {
                    $actor_data = json_decode($result['data'], true);
                    if (is_array($actor_data)) {
                        $actor_list[] = [
                            'name' => $actor_data['name'] ?? $actor_name,
                            'bio' => $actor_data['content'] ?? '',
                            'image' => '' // 暂时不处理图片
                        ];
                    }
                }
            }
        }
        
        if (!empty($actor_list)) {
            return ['code' => 1, 'actors' => $actor_list];
        }
        return ['code' => 0, 'msg' => '未生成演员信息'];
    }

    public function generateReviews($vod_name)
    {
        $prompt = $this->buildReviewPrompt($vod_name);
        $result = $this->generateContent($prompt, 'review');
        if ($result['code'] == 1) {
            // 简单处理，将生成的影评作为一篇
            return ['code' => 1, 'reviews' => [
                [
                    'title' => "《{$vod_name}》影评",
                    'content' => $result['data']
                ]
            ]];
        }
        return $result;
    }

    public function generateEpisodes($vod_name)
    {
        $prompt = $this->buildPlotPrompt($vod_name, 10);
        $result = $this->generateContent($prompt, 'plot');
        if ($result['code'] == 1) {
            $episodes = json_decode($result['data'], true);
            if (is_array($episodes)) {
                $formatted_episodes = [];
                foreach ($episodes as $episode) {
                    $formatted_episodes[] = [
                        'title' => $episode['name'] ?? '',
                        'content' => $episode['detail'] ?? ''
                    ];
                }
                return ['code' => 1, 'episodes' => $formatted_episodes];
            }
        }
        return $result;
    }
}
