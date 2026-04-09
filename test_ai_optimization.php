<?php
/**
 * AI优化功能测试脚本
 * 作者：阿木
 * 网址：Amu5.Com
 * QQ：46552292
 * 功能：测试AI生成优化功能
 */

// 加载框架核心
require_once __DIR__ . '/thinkphp/base.php';

// 手动加载必要的类
use think\Loader;

// 注册命名空间
Loader::addNamespace('app', __DIR__ . '/application');

use app\common\util\AiContentManager;

// 测试类
class TestAiOptimization
{
    protected $aiContentManager;
    
    public function __construct()
    {
        // 初始化AI内容管理器
        $this->aiContentManager = new AiContentManager();
    }
    
    /**
     * 测试配置管理功能
     */
    public function testConfigManagement()
    {
        echo "\n=== 测试配置管理功能 ===\n";
        
        // 测试获取默认配置
        $config = $this->aiContentManager->getGenerateConfig('vod', 'blurb');
        echo "默认配置: " . json_encode($config, JSON_UNESCAPED_UNICODE) . "\n";
        
        // 测试设置配置
        $customConfig = [
            'enabled' => true,
            'prompt' => '测试自定义提示词',
            'model' => 'gpt-4o-mini',
            'temperature' => 0.8,
            'max_tokens' => 1000
        ];
        
        $result = $this->aiContentManager->setGenerateConfig('vod', 'blurb', $customConfig);
        echo "设置配置结果: " . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
        
        // 测试获取配置
        $updatedConfig = $this->aiContentManager->getGenerateConfig('vod', 'blurb');
        echo "更新后配置: " . json_encode($updatedConfig, JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    /**
     * 测试生成状态跟踪
     */
    public function testGenerateStatus()
    {
        echo "\n=== 测试生成状态跟踪 ===\n";
        
        // 测试标记为已生成
        $result = $this->aiContentManager->clearGeneratedMark('vod', 1, 'blurb');
        echo "清除生成标记结果: " . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
        
        // 这里可以添加更多状态跟踪测试
    }
    
    /**
     * 测试历史记录管理
     */
    public function testHistoryManagement()
    {
        echo "\n=== 测试历史记录管理 ===\n";
        
        // 测试获取历史列表
        $history = $this->aiContentManager->getHistoryList([], 1, 10);
        echo "历史记录数量: " . $history['total'] . "\n";
        
        // 测试获取统计信息
        $stats = $this->aiContentManager->getStatistics();
        echo "生成统计: " . json_encode($stats, JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    /**
     * 测试批量生成功能
     */
    public function testBatchGenerate()
    {
        echo "\n=== 测试批量生成功能 ===\n";
        
        // 测试批量生成
        $ids = [1, 2, 3];
        $fields = ['blurb', 'tags'];
        $options = [
            'force_generate' => false
        ];
        
        $result = $this->aiContentManager->batchGenerate('vod', $ids, $fields, $options);
        echo "批量生成结果: " . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    /**
     * 运行所有测试
     */
    public function runAllTests()
    {
        echo "开始测试AI优化功能...\n";
        
        try {
            $this->testConfigManagement();
            $this->testGenerateStatus();
            $this->testHistoryManagement();
            $this->testBatchGenerate();
            
            echo "\n=== 所有测试完成 ===\n";
        } catch (Exception $e) {
            echo "测试失败: " . $e->getMessage() . "\n";
        }
    }
}

// 运行测试
$test = new TestAiOptimization();
$test->runAllTests();
