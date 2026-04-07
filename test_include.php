<?php

// 测试脚本：逐步包含核心文件以定位语法错误

echo "Testing core files...\n";

// 测试 1: 测试 PHP 基本功能
echo "Test 1: PHP basic functionality... ";
try {
    echo "OK\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// 测试 2: 测试 ThinkPHP 基础文件
echo "Test 2: ThinkPHP base file... ";
try {
    require_once __DIR__ . '/thinkphp/base.php';
    echo "OK\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// 测试 3: 测试配置文件
echo "Test 3: Config files... ";
try {
    require_once __DIR__ . '/application/config.php';
    require_once __DIR__ . '/application/database.php';
    echo "OK\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// 测试 4: 测试公共文件
echo "Test 4: Common files... ";
try {
    require_once __DIR__ . '/application/common.php';
    echo "OK\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Testing completed.\n";
?>