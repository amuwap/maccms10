<?php
// 简单测试脚本，用于定位api常量未定义的错误

// 首先定义api常量
define('api', 'api');

echo 'api常量已定义: ' . api . '\n';

// 尝试加载配置文件
$config = include __DIR__ . '/application/extra/maccms.php';
echo '配置文件加载完成\n';

// 尝试访问upload配置
echo 'Upload配置存在: ' . (isset($config['upload']) ? 'Yes' : 'No') . '\n';

// 尝试访问api配置
echo 'API配置存在: ' . (isset($config['api']) ? 'Yes' : 'No') . '\n';

// 尝试访问upload中的api配置
echo 'Upload API配置存在: ' . (isset($config['upload']['api']) ? 'Yes' : 'No') . '\n';

// 测试直接使用api作为常量
echo '直接使用api常量: ' . api . '\n';

// 测试在数组访问中使用api作为常量
$test_array = ['upload' => ['api' => ['test' => 'value']]];
echo '在数组访问中使用api常量: ' . $test_array['upload'][api]['test'] . '\n';

echo '测试完成，没有api常量未定义错误！\n';
