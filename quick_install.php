<?php
/**
 * 苹果CMS快速安装脚本
 * 作者：阿木
 * 网址：Amu5.Com
 * QQ：46552292
 * 兼容：PHP 7.4-8.5，MySQL 5.7-8.0
 */

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');

// 检查是否已安装
if (file_exists(APP_PATH . 'data/install/install.lock')) {
    echo '<h1>系统已安装</h1>';
    echo '<p>如需重新安装，请删除 ' . APP_PATH . 'data/install/install.lock 文件</p>';
    echo '<p><a href="index.php">访问首页</a> | <a href="admin.php">进入后台</a></p>';
    exit;
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据
    $hostname = $_POST['hostname'] ?? '127.0.0.1';
    $hostport = $_POST['hostport'] ?? '3306';
    $database = $_POST['database'] ?? 'maccms10';
    $username = $_POST['username'] ?? 'root';
    $password = $_POST['password'] ?? '';
    $prefix = $_POST['prefix'] ?? 'mac_';
    $admin_account = $_POST['admin_account'] ?? 'admin';
    $admin_password = $_POST['admin_password'] ?? 'admin123';
    
    // 环境检测
    $errors = [];
    
    // 检查PHP版本
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        $errors[] = 'PHP版本必须大于等于7.4';
    }
    
    // 检查必要扩展
    $required_extensions = ['pdo', 'pdo_mysql', 'fileinfo', 'curl', 'gd'];
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = '缺少必要的PHP扩展：' . $ext;
        }
    }
    
    // 检查目录权限
    $required_dirs = [
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
    
    foreach ($required_dirs as $dir) {
        // 检查目录是否存在，如果不存在则创建
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                $errors[] = '创建目录失败：' . $dir;
                continue;
            }
        }
        // 检查目录是否可写
        if (!is_writable($dir)) {
            // 尝试设置权限
            if (!chmod($dir, 0755)) {
                $errors[] = '目录不可写：' . $dir;
            }
        }
    }
    
    // 显示错误信息
    if (!empty($errors)) {
        echo '<h1>安装失败</h1>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul>';
        echo '<a href="quick_install.php">返回重试</a>';
        exit;
    }
    
    // 连接数据库
    try {
        $dsn = "mysql:host={$hostname};port={$hostport};charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 创建数据库
        $sql = "CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $pdo->exec($sql);
        
        // 选择数据库
        $pdo->exec("USE `{$database}`");
        
        // 导入SQL文件
        $sql_files = [
            APP_PATH . 'install/sql/install.sql',
            APP_PATH . 'install/sql/extend.sql',
            APP_PATH . 'install/sql/new_types.sql'
        ];
        
        foreach ($sql_files as $sql_file) {
            if (file_exists($sql_file)) {
                $sql = file_get_contents($sql_file);
                $sql = str_replace('mac_', $prefix, $sql);
                $pdo->exec($sql);
            }
        }
        
        // 创建管理员账号
        $password_hash = md5($admin_password);
        $sql = "INSERT INTO `{$prefix}admin` (`admin_name`, `admin_pwd`, `admin_status`) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$admin_account, $password_hash, 1]);
        
        // 生成数据库配置文件
        $config_content = <<<EOF
<?php
return [
    'type'            => 'mysql',
    'hostname'        => '{$hostname}',
    'database'        => '{$database}',
    'username'        => '{$username}',
    'password'        => '{$password}',
    'hostport'        => '{$hostport}',
    'dsn'             => '',
    'params'          => [],
    'charset'         => 'utf8mb4',
    'prefix'          => '{$prefix}',
    'debug'           => false,
    'deploy'          => 0,
    'rw_separate'     => false,
    'master_num'      => 1,
    'slave_no'        => '',
    'fields_strict'   => false,
    'resultset_type'  => 'array',
    'auto_timestamp'  => false,
    'datetime_format' => 'Y-m-d H:i:s',
    'sql_explain'     => false,
    'builder'         => '',
    'query'           => '\\think\\db\\Query',
];
EOF;
        
        file_put_contents(APP_PATH . 'database.php', $config_content);
        
        // 设置目录权限
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
        
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            chmod($directory, 0755);
        }
        
        // 创建安装锁文件
        file_put_contents(APP_PATH . 'data/install/install.lock', date('Y-m-d H:i:s'));
        
        // 显示安装成功信息
        $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
        echo '<h1>安装成功</h1>';
        echo '<p>🎉 苹果CMS安装完成！</p>';
        echo '<p><strong>后台地址：</strong><a href="admin.php">' . $site_url . '/admin.php</a></p>';
        echo '<p><strong>管理员账号：</strong>' . $admin_account . '</p>';
        echo '<p><strong>管理员密码：</strong>' . $admin_password . '</p>';
        echo '<p><strong>数据库名称：</strong>' . $database . '</p>';
        echo '<p><strong>数据表前缀：</strong>' . $prefix . '</p>';
        echo '<p>系统已自动设置目录权限，现在可以正常使用了！</p>';
        echo '<p>请及时修改默认管理员密码，以保证系统安全！</p>';
        echo '<p><a href="index.php">访问首页</a> | <a href="admin.php">进入后台</a></p>';
        
    } catch (PDOException $e) {
        echo '<h1>安装失败</h1>';
        echo '<p>数据库连接失败：' . $e->getMessage() . '</p>';
        echo '<p>请检查数据库账号密码是否正确，以及MySQL服务是否启动</p>';
        echo '<a href="quick_install.php">返回重试</a>';
    }
} else {
    // 显示安装表单
    echo '<!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>苹果CMS快速安装</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { text-align: center; color: #333; }
            .form-group { margin-bottom: 20px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
            button { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
            button:hover { background-color: #0069d9; }
            .note { font-size: 12px; color: #666; margin-top: 5px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>苹果CMS快速安装</h1>
            <form method="post">
                <div class="form-group">
                    <label>数据库服务器</label>
                    <input type="text" name="hostname" value="127.0.0.1" placeholder="数据库服务器地址">
                </div>
                <div class="form-group">
                    <label>数据库端口</label>
                    <input type="text" name="hostport" value="3306" placeholder="数据库端口">
                </div>
                <div class="form-group">
                    <label>数据库名称</label>
                    <input type="text" name="database" value="maccms10" placeholder="数据库名称">
                </div>
                <div class="form-group">
                    <label>数据库用户名</label>
                    <input type="text" name="username" value="root" placeholder="数据库用户名">
                </div>
                <div class="form-group">
                    <label>数据库密码</label>
                    <input type="password" name="password" placeholder="数据库密码">
                    <div class="note">如果数据库没有密码，请留空</div>
                </div>
                <div class="form-group">
                    <label>数据表前缀</label>
                    <input type="text" name="prefix" value="mac_" placeholder="数据表前缀">
                </div>
                <div class="form-group">
                    <label>管理员账号</label>
                    <input type="text" name="admin_account" value="admin" placeholder="管理员账号">
                </div>
                <div class="form-group">
                    <label>管理员密码</label>
                    <input type="password" name="admin_password" value="admin123" placeholder="管理员密码">
                </div>
                <button type="submit">开始安装</button>
            </form>
        </div>
    </body>
    </html>';
}
