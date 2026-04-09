<?php
/**
 * 苹果CMS权限修复脚本
 * 作者：阿木
 * 网址：Amu5.Com
 * QQ：46552292
 * 功能：修复目录权限，确保web服务器用户可以写入
 */

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');

// 需要修复权限的目录列表
$directories = [
    APP_PATH,
    APP_PATH . 'data/',
    APP_PATH . 'data/config/',
    APP_PATH . 'data/backup/',
    APP_PATH . 'data/update/',
    APP_PATH . 'data/install/',
    __DIR__ . '/runtime/',
    __DIR__ . '/runtime/cache/',
    __DIR__ . '/runtime/log/',
    __DIR__ . '/runtime/temp/',
    __DIR__ . '/upload/'
];

echo '<h1>苹果CMS权限修复</h1>';
echo '<ul>';

foreach ($directories as $directory) {
    // 检查目录是否存在
    if (!is_dir($directory)) {
        // 创建目录
        if (mkdir($directory, 0755, true)) {
            echo '<li>创建目录: ' . $directory . '</li>';
        } else {
            echo '<li style="color: red;">创建目录失败: ' . $directory . '</li>';
            continue;
        }
    }
    
    // 修复权限
    if (chmod($directory, 0755)) {
        echo '<li>修复权限: ' . $directory . ' (0755)</li>';
    } else {
        echo '<li style="color: red;">修复权限失败: ' . $directory . '</li>';
    }
    
    // 检查子目录
    if (is_dir($directory)) {
        $subdirs = glob($directory . '/*', GLOB_ONLYDIR);
        foreach ($subdirs as $subdir) {
            if (chmod($subdir, 0755)) {
                echo '<li>修复子目录权限: ' . $subdir . ' (0755)</li>';
            }
        }
    }
}

echo '</ul>';
echo '<h2>修复完成</h2>';
echo '<p>所有必要的目录权限已修复，现在应该可以正常写入缓存了。</p>';
echo '<p><a href="index.php">访问首页</a> | <a href="admin.php">进入后台</a></p>';
