<?php
// 测试脚本，用于定位api常量未定义的错误

// 首先定义api常量
define('api', 'api');

echo 'api常量已定义: ' . api . '<br>';

// 尝试包含必要的文件
require_once __DIR__ . '/thinkphp/start.php';

echo '系统初始化完成<br>';

// 尝试访问配置
$config = config('maccms');
echo '配置加载完成<br>';

// 尝试访问upload配置
echo 'Upload配置: ' . print_r($config['upload'], true) . '<br>';

// 尝试访问api配置
echo 'API配置: ' . print_r($config['api'], true) . '<br>';

// 尝试访问upload中的api配置
echo 'Upload API配置: ' . print_r($config['upload']['api'], true) . '<br>';
