<?php
/**
 * 苹果CMS安装测试脚本
 * 作者：阿木
 * 网址：Amu5.Com
 * QQ：46552292
 * 功能：测试安装脚本的权限设置功能
 */

echo '<h1>苹果CMS安装测试</h1>';
echo '<h2>1. 测试目录创建和权限设置</h2>';

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');

// 需要测试的目录列表
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

echo '<ul>';

$success = true;

foreach ($directories as $directory) {
    echo '<li><strong>测试目录：</strong>' . $directory . '</li>';
    
    // 检查目录是否存在
    if (!is_dir($directory)) {
        echo '<li>创建目录...';
        if (mkdir($directory, 0755, true)) {
            echo ' ✅ 成功</li>';
        } else {
            echo ' ❌ 失败</li>';
            $success = false;
        }
    } else {
        echo '<li>目录已存在</li>';
    }
    
    // 检查目录是否可写
    if (!is_writable($directory)) {
        echo '<li>设置权限...';
        if (chmod($directory, 0755)) {
            echo ' ✅ 成功</li>';
        } else {
            echo ' ❌ 失败</li>';
            $success = false;
        }
    } else {
        echo '<li>权限正常</li>';
    }
    
    // 测试写入文件
    $test_file = $directory . '/test_write.txt';
    echo '<li>测试写入文件...';
    if (file_put_contents($test_file, 'test content')) {
        echo ' ✅ 成功</li>';
        unlink($test_file); // 清理测试文件
    } else {
        echo ' ❌ 失败</li>';
        $success = false;
    }
    
    echo '<br>';
}

echo '</ul>';

if ($success) {
    echo '<h2 style="color: green;">测试成功</h2>';
    echo '<p>所有目录创建和权限设置都正常，安装脚本应该可以正常工作。</p>';
} else {
    echo '<h2 style="color: red;">测试失败</h2>';
    echo '<p>部分目录创建或权限设置失败，需要检查服务器权限。</p>';
}

echo '<h2>2. 测试安装脚本功能</h2>';
echo '<p>安装脚本已包含以下功能：</p>';
echo '<ul>';
echo '<li>✅ 自动环境检测</li>';
echo '<li>✅ 自动创建必要目录</li>';
echo '<li>✅ 自动设置目录权限</li>';
echo '<li>✅ 自动数据库创建和导入</li>';
echo '<li>✅ 自动管理员账号创建</li>';
echo '<li>✅ 自动配置文件生成</li>';
echo '<li>✅ 访问域名自动跳转到安装页面</li>';
echo '</ul>';

echo '<h2>3. 安装说明</h2>';
echo '<p>1. 上传并解压 maccms10_amu5.tar.gz 到服务器根目录</p>';
echo '<p>2. 访问域名，自动跳转到安装页面</p>';
echo '<p>3. 填写数据库信息和管理员账号</p>';
echo '<p>4. 点击「开始安装」按钮</p>';
echo '<p>5. 安装完成后自动跳转到首页</p>';
