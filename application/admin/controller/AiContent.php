<?php
namespace app\admin\controller;

use app\common\model\Vod;
use app\common\util\AutoContentGenerator;
use app\common\util\AiService;

class AiContent extends Base {
    public function __construct() {
        parent::__construct();
        $this->assign('menu', 'ai_content');
    }

    /**
     * AI内容管理首页
     */
    public function index() {
        $this->assign('title', 'AI内容管理');
        return $this->fetch('ai_content/index');
    }

    /**
     * 批量生成内容
     */
    public function batchGenerate() {
        if ($this->request->isPost()) {
            $vod_ids = $this->request->post('vod_ids', '');
            $types = $this->request->post('types', 'intro,tags,comments');
            
            if (empty($vod_ids)) {
                return $this->error('请选择要生成内容的影视');
            }
            
            $vod_ids = explode(',', $vod_ids);
            $types = explode(',', $types);
            
            $generator = new AutoContentGenerator();
            $result = $generator->batchGenerateContent($vod_ids, $types);
            
            if ($result['code'] == 1) {
                return $this->success('批量生成任务已开始', url('index'));
            } else {
                return $this->error($result['msg']);
            }
        }
        
        // 获取影视列表
        $vod_list = model('Vod')->where(['vod_status' => 1])->order('vod_time desc')->limit(50)->select();
        $this->assign('vod_list', $vod_list);
        $this->assign('title', '批量生成内容');
        return $this->fetch('ai_content/batch_generate');
    }

    /**
     * 生成指定影视的内容
     */
    public function generate() {
        $vod_id = $this->request->param('vod_id', 0);
        $type = $this->request->param('type', 'all');
        
        if (empty($vod_id)) {
            return $this->error('请选择影视');
        }
        
        $generator = new AutoContentGenerator();
        
        if ($type == 'all') {
            $result = $generator->generateContentForVod($vod_id);
        } else {
            $result = $generator->generateSpecificContent($vod_id, $type);
        }
        
        if ($result['code'] == 1) {
            return $this->success($result['msg'], url('index'));
        } else {
            return $this->error($result['msg']);
        }
    }

    /**
     * 清理生成的内容
     */
    public function clear() {
        $vod_id = $this->request->param('vod_id', 0);
        
        if (empty($vod_id)) {
            return $this->error('请选择影视');
        }
        
        $generator = new AutoContentGenerator();
        $result = $generator->clearGeneratedContent($vod_id);
        
        if ($result['code'] == 1) {
            return $this->success($result['msg'], url('index'));
        } else {
            return $this->error($result['msg']);
        }
    }

    /**
     * 查看生成状态
     */
    public function status() {
        $vod_id = $this->request->param('vod_id', 0);
        
        if (empty($vod_id)) {
            return $this->error('请选择影视');
        }
        
        $generator = new AutoContentGenerator();
        $result = $generator->getGenerateStatus($vod_id);
        
        if ($result['code'] == 1) {
            $this->assign('status', $result['data']);
            $this->assign('vod_id', $vod_id);
            return $this->fetch('ai_content/status');
        } else {
            return $this->error($result['msg']);
        }
    }

    /**
     * AI设置
     */
    public function setting() {
        if ($this->request->isPost()) {
            $config = $this->request->post();
            
            // 保存设置
            foreach ($config as $key => $value) {
                model('System')->where(['name' => $key])->update(['value' => $value]);
            }
            
            return $this->success('设置保存成功', url('setting'));
        }
        
        // 获取当前设置
        $settings = [];
        $keys = [
            'ai_auto_generate',
            'ai_generate_types',
            'ai_batch_size',
            'ai_max_retries',
            'ai_cache_time'
        ];
        
        foreach ($keys as $key) {
            $setting = model('System')->where(['name' => $key])->find();
            $settings[$key] = $setting ? $setting['value'] : '';
        }
        
        $this->assign('settings', $settings);
        $this->assign('title', 'AI设置');
        return $this->fetch('ai_content/setting');
    }

    /**
     * 测试AI连接
     */
    public function testConnection() {
        $aiService = new AiService();
        $result = $aiService->testConnection();
        
        if ($result['code'] == 1) {
            return $this->success($result['msg']);
        } else {
            return $this->error($result['msg']);
        }
    }

    /**
     * 获取可用模型
     */
    public function getModels() {
        $aiService = new AiService();
        $result = $aiService->listAvailableModels();
        
        if ($result['code'] == 1) {
            return json($result);
        } else {
            return json($result);
        }
    }
}