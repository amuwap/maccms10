<?php
/**
 * 苹果CMS一键安装程序
 * 作者：阿木
 * 网址：Amu5.Com
 * QQ：46552292
 * 兼容：PHP 7.4-8.5，MySQL 5.7-8.0
 */

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
// 定义项目路径
define('ROOT_PATH', __DIR__ . '/');

// 检查是否已安装
if (file_exists(APP_PATH . 'data/install/install.lock')) {
    echo '<h1>系统已安装</h1>';
    echo '<p>如需重新安装，请删除 ' . APP_PATH . 'data/install/install.lock 文件</p>';
    echo '<p><a href="index.php">访问首页</a> | <a href="admin.php">进入后台</a></p>';
    exit;
}

// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';