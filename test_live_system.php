<?php
/**
 * 无人直播系统测试脚本
 */

// 加载ThinkPHP框架
require_once 'thinkphp/start.php';

use app\common\util\LiveService;
use app\common\model\Live as LiveModel;

// 测试创建无人直播
function testCreateFakeLive() {
    echo "=== 测试创建无人直播 ===\n";
    
    $testLives = [
        [
            'live_name' => '游戏直播测试',
            'live_title' => '王者荣耀精彩操作教学',
            'live_content' => '今天给大家带来王者荣耀的高端操作教学，包括英雄技巧和团队配合',
            'live_video_url' => 'https://example.com/game.mp4',
            'live_cover' => 'https://example.com/game_cover.jpg',
            'type_id' => 1
        ],
        [
            'live_name' => '音乐直播测试',
            'live_title' => '流行歌曲翻唱',
            'live_content' => '为大家带来最新流行歌曲的翻唱，希望大家喜欢',
            'live_video_url' => 'https://example.com/music.mp4',
            'live_cover' => 'https://example.com/music_cover.jpg',
            'type_id' => 2
        ],
        [
            'live_name' => '教程直播测试',
            'live_title' => 'PHP编程入门教程',
            'live_content' => '从零开始学习PHP编程，适合初学者',
            'live_video_url' => 'https://example.com/tutorial.mp4',
            'live_cover' => 'https://example.com/tutorial_cover.jpg',
            'type_id' => 3
        ]
    ];
    
    foreach ($testLives as $liveData) {
        $result = LiveService::createFakeLive($liveData);
        if ($result['code'] == 1) {
            echo "创建直播成功: {$liveData['live_name']} (ID: {$result['data']['live_id']})\n";
        } else {
            echo "创建直播失败: {$liveData['live_name']} - {$result['msg']}\n";
        }
    }
}

// 测试自动互动
function testAutoInteraction() {
    echo "\n=== 测试自动互动 ===\n";
    
    // 获取所有无人直播
    $lives = LiveModel::where('live_is_fake', 1)->select();
    
    foreach ($lives as $live) {
        echo "测试直播: {$live['live_name']} (ID: {$live['live_id']})\n";
        
        // 测试多次自动互动
        for ($i = 0; $i < 5; $i++) {
            $result = LiveService::simulateAutoInteraction($live['live_id']);
            echo "  互动类型: {$result['type']} - {$result['msg']}\n";
            // 模拟时间间隔
            usleep(500000); // 0.5秒
        }
        
        // 测试获取评论和弹幕
        $comments = LiveService::getFakeComments($live['live_id']);
        $danmakus = LiveService::getDanmakus($live['live_id']);
        $gifts = LiveService::getLiveGifts($live['live_id']);
        
        echo "  生成的评论数: " . count($comments) . "\n";
        echo "  生成的弹幕数: " . count($danmakus) . "\n";
        echo "  生成的礼物数: " . count($gifts) . "\n";
    }
}

// 测试直播统计
function testLiveStatistics() {
    echo "\n=== 测试直播统计 ===\n";
    
    $lives = LiveModel::where('live_is_fake', 1)->select();
    
    foreach ($lives as $live) {
        $stats = LiveService::getFakeLiveStatistics($live['live_id']);
        if ($stats['code'] == 1) {
            echo "直播: {$live['live_name']}\n";
            echo "  观众数: {$stats['data']['viewers']}\n";
            echo "  点赞数: {$stats['data']['likes']}\n";
            echo "  评论数: {$stats['data']['comments']}\n";
        }
    }
}

// 测试观众多样性
function testViewerDiversity() {
    echo "\n=== 测试观众多样性 ===\n";
    
    $lives = LiveModel::where('live_is_fake', 1)->select();
    
    foreach ($lives as $live) {
        $diversity = LiveService::getViewerDiversity($live['live_id']);
        if ($diversity['code'] == 1) {
            echo "直播: {$live['live_name']}\n";
            foreach ($diversity['data'] as $type => $info) {
                echo "  {$info['name']}: {$info['count']}人 ({$info['percentage']}%)\n";
            }
        }
    }
}

// 测试礼物排行
function testGiftRanking() {
    echo "\n=== 测试礼物排行 ===\n";
    
    $lives = LiveModel::where('live_is_fake', 1)->select();
    
    foreach ($lives as $live) {
        $ranking = LiveService::getGiftRanking($live['live_id']);
        echo "直播: {$live['live_name']}\n";
        if (empty($ranking)) {
            echo "  暂无礼物数据\n";
        } else {
            foreach ($ranking as $index => $item) {
                echo "  第" . ($index + 1) . "名: {$item['username']} - 总价值: {$item['total']}\n";
            }
        }
    }
}

// 运行测试
echo "开始测试无人直播系统...\n\n";

testCreateFakeLive();
testAutoInteraction();
testLiveStatistics();
testViewerDiversity();
testGiftRanking();

echo "\n测试完成！\n";
?>