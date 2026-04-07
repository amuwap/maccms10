<?php
/**
 * 无人直播系统优化测试脚本
 */

// 手动加载必要的文件
require_once 'thinkphp/base.php';

// 直接包含LiveService文件
require_once 'application/common/util/LiveService.php';

// 注册自动加载
\think\Loader::register();

// 导入必要的类
use app\common\util\LiveService;

// 测试智能互动优化
function testIntelligentInteraction() {
    echo "=== 测试无人直播智能互动优化 ===\n";
    
    // 测试智能评论生成
    echo "\n1. 测试智能评论生成:\n";
    $contexts = ['游戏', '音乐', '教程'];
    foreach ($contexts as $context) {
        echo "\n   上下文: {$context}\n";
        for ($i = 0; $i < 3; $i++) {
            $comment = LiveService::generateCustomComment(1, $context);
            echo "   评论" . ($i+1) . ": {$comment}\n";
        }
    }
    
    // 测试用户等级系统
    echo "\n2. 测试用户等级系统:\n";
    $pointsList = [0, 100, 500, 1000, 5000, 10000];
    foreach ($pointsList as $points) {
        $level = LiveService::getUserLevel($points);
        echo "   {$points}分: Lv.{$level['level']} {$level['name']} {$level['icon']}\n";
    }
    
    // 测试虚拟礼物系统
    echo "\n3. 测试虚拟礼物系统:\n";
    $gifts = LiveService::getVirtualGifts();
    if ($gifts['code'] == 1) {
        echo "   所有礼物:\n";
        foreach ($gifts['data'] as $gift) {
            echo "   - {$gift['name']} ({$gift['icon']}) - {$gift['price']}分\n";
        }
    }
    
    // 测试超级礼物
    echo "\n4. 测试超级礼物:\n";
    $superGifts = LiveService::getSuperGifts();
    if ($superGifts['code'] == 1) {
        echo "   超级礼物:\n";
        foreach ($superGifts['data'] as $gift) {
            echo "   - {$gift['name']} ({$gift['icon']}) - {$gift['price']}分\n";
        }
    }
    
    // 测试抽奖系统
    echo "\n5. 测试抽奖系统:\n";
    $prizes = LiveService::getLotteryPrizes();
    echo "   抽奖奖品:\n";
    foreach ($prizes as $prize) {
        echo "   - {$prize['name']} - 概率: " . number_format($prize['probability'] * 100, 2) . "%\n";
    }
}

// 运行测试
echo "开始测试无人直播系统优化效果...\n\n";
testIntelligentInteraction();
echo "\n测试完成！\n";
?>