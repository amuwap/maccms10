<?php
/**
 * 无人直播系统简单测试脚本
 */

// 手动加载必要的文件
require_once 'thinkphp/base.php';

// 直接包含LiveService文件
require_once 'application/common/util/LiveService.php';

// 注册自动加载
\think\Loader::register();

// 导入必要的类
use app\common\util\LiveService;

// 测试LiveService的核心功能
function testLiveService() {
    echo "=== 测试LiveService核心功能 ===\n";
    
    // 测试生成智能评论
    echo "\n1. 测试生成智能评论:\n";
    $context = '游戏';
    $comment = LiveService::generateCustomComment(1, $context);
    echo "   上下文: {$context}\n";
    echo "   生成的评论: {$comment}\n";
    
    // 测试用户等级
    echo "\n2. 测试用户等级:\n";
    $pointsList = [0, 100, 500, 1000, 5000, 10000];
    foreach ($pointsList as $points) {
        $level = LiveService::getUserLevel($points);
        echo "   {$points}分: Lv.{$level['level']} {$level['name']} {$level['icon']}\n";
    }
    
    // 测试获取虚拟礼物列表
    echo "\n3. 测试获取虚拟礼物列表:\n";
    $gifts = LiveService::getVirtualGifts();
    if ($gifts['code'] == 1) {
        foreach ($gifts['data'] as $gift) {
            echo "   {$gift['name']} ({$gift['icon']}) - {$gift['price']}分\n";
        }
    }
    
    // 测试获取超级礼物
    echo "\n4. 测试获取超级礼物:\n";
    $superGifts = LiveService::getSuperGifts();
    if ($superGifts['code'] == 1) {
        foreach ($superGifts['data'] as $gift) {
            echo "   {$gift['name']} ({$gift['icon']}) - {$gift['price']}分\n";
        }
    }
    
    // 测试获取抽奖奖品
    echo "\n5. 测试获取抽奖奖品:\n";
    $prizes = LiveService::getLotteryPrizes();
    foreach ($prizes as $prize) {
        echo "   {$prize['name']} - 概率: " . number_format($prize['probability'] * 100, 2) . "%\n";
    }
}

// 运行测试
echo "开始测试无人直播系统核心功能...\n\n";
testLiveService();
echo "\n测试完成！\n";
?>