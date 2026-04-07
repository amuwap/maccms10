<?php
// 模拟ThinkPHP的启动过程

// 定义应用目录
define('ROOT_PATH', __DIR__ . '/');
define('APP_PATH', __DIR__ . '/application/');
define('ENTRANCE', 'admin');

// 加载框架引导文件
echo "Loading thinkphp/start.php...\n";
require __DIR__ . '/thinkphp/start.php';
echo "ThinkPHP started successfully!\n";
?>