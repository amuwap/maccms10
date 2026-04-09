<?php
/**
 * 苹果CMS一键安装脚本
 * 作者：阿木
 * 网址：Amu5.Com
 * QQ：46552292
 * 兼容：PHP 7.4-8.5，MySQL 5.7-8.0
 */

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
// 定义项目路径
define('ROOT_PATH', __DIR__ . '/');
// 定义入口类型
define('ENTRANCE', 'install');

// 检查是否已安装
if (file_exists(APP_PATH . 'data/install/install.lock')) {
    echo '<!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>系统已安装 - 苹果CMS</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; color: #333; }
            .container { max-width: 800px; margin: 50px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #333; margin-bottom: 30px; text-align: center; }
            p { line-height: 1.6; margin-bottom: 20px; }
            .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.3s; }
            .btn:hover { background-color: #0069d9; }
            .btn-secondary { background-color: #6c757d; margin-left: 10px; }
            .btn-secondary:hover { background-color: #5a6268; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>系统已安装</h1>
            <p>如需重新安装，请删除 <code>" . APP_PATH . "data/install/install.lock</code> 文件</p>
            <p><a href="index.php" class="btn">访问首页</a> <a href="admin.php" class="btn btn-secondary">进入后台</a></p>
        </div>
    </body>
    </html>';
    exit;
}

// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = [
        'hostname' => $_POST['hostname'],
        'hostport' => $_POST['hostport'],
        'database' => $_POST['database'],
        'username' => $_POST['username'],
        'password' => $_POST['password'],
        'prefix' => $_POST['prefix'],
        'admin_account' => $_POST['admin_account'],
        'admin_password' => $_POST['admin_password']
    ];
    
    // 环境检测
    $errors = [];
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        $errors[] = 'PHP版本必须大于等于7.4';
    }
    
    $required_extensions = ['pdo', 'pdo_mysql', 'fileinfo', 'curl', 'gd'];
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = '缺少必要的PHP扩展：' . $ext;
        }
    }
    
    $required_dirs = [
        APP_PATH,
        APP_PATH . 'data/',
        APP_PATH . 'data/config/',
        APP_PATH . 'data/backup/',
        APP_PATH . 'data/update/',
        APP_PATH . 'runtime/',
        APP_PATH . 'upload/'
    ];
    
    foreach ($required_dirs as $dir) {
        if (!is_writable($dir)) {
            $errors[] = '目录不可写：' . $dir;
        }
    }
    
    if (!empty($errors)) {
        echo '<!DOCTYPE html>
        <html lang="zh-CN">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>安装失败 - 苹果CMS</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; color: #333; }
                .container { max-width: 800px; margin: 50px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #dc3545; margin-bottom: 30px; text-align: center; }
                .alert-danger { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
                .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.3s; }
                .btn:hover { background-color: #0069d9; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>安装失败</h1>
                <div class="alert-danger">
                    <h3>环境检测失败</h3>
                    <ul>';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul>
                </div>
                <a href="one_click_install.php" class="btn">返回重试</a>
            </div>
        </body>
        </html>';
        exit;
    }
    
    // 连接数据库
    try {
        $dsn = "mysql:host={$config['hostname']};port={$config['hostport']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 创建数据库
        $sql = "CREATE DATABASE IF NOT EXISTS `{$config['database']}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $pdo->exec($sql);
        
        // 选择数据库
        $pdo->exec("USE `{$config['database']}`");
        
        // 导入SQL文件
        $sql_files = [
            APP_PATH . 'install/sql/install.sql',
            APP_PATH . 'install/sql/extend.sql',
            APP_PATH . 'install/sql/new_types.sql'
        ];
        
        foreach ($sql_files as $sql_file) {
            if (file_exists($sql_file)) {
                $sql = file_get_contents($sql_file);
                $sql = str_replace('mac_', $config['prefix'], $sql);
                $pdo->exec($sql);
            }
        }
        
        // 创建管理员账号
        $password_hash = md5($config['admin_password']);
        $sql = "INSERT INTO `{$config['prefix']}admin` (`admin_name`, `admin_pwd`, `admin_status`) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$config['admin_account'], $password_hash, 1]);
        
        // 生成数据库配置文件
        $code = <<<INFO
<?php
/**
 * 数据库配置文件
 * 作者：阿木
 * 网址：Amu5.Com
 * QQ：46552292
 * 兼容：PHP 7.4-8.5，MySQL 5.7-8.0
 */
return [
    'type'            => 'mysql',
    'hostname'        => '{$config['hostname']}',
    'database'        => '{$config['database']}',
    'username'        => '{$config['username']}',
    'password'        => '{$config['password']}',
    'hostport'        => '{$config['hostport']}',
    'dsn'             => '',
    'params'          => [],
    'charset'         => 'utf8mb4',
    'prefix'          => '{$config['prefix']}',
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
INFO;
        
        file_put_contents(APP_PATH . 'database.php', $code);
        
        // 创建安装锁文件
        file_put_contents(APP_PATH . 'data/install/install.lock', date('Y-m-d H:i:s'));
        
        // 显示安装成功页面
        $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
        echo '<!DOCTYPE html>
        <html lang="zh-CN">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>安装成功 - 苹果CMS</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; color: #333; }
                .container { max-width: 800px; margin: 50px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #28a745; margin-bottom: 30px; text-align: center; }
                .alert-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 20px; margin-bottom: 30px; border-radius: 4px; }
                .info-item { display: flex; margin-bottom: 15px; }
                .info-label { width: 120px; font-weight: 500; color: #555; }
                .info-value { flex: 1; }
                .btn { display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.3s; margin-right: 10px; }
                .btn:hover { background-color: #0069d9; }
                .btn-secondary { background-color: #6c757d; }
                .btn-secondary:hover { background-color: #5a6268; }
                .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; margin-top: 30px; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>安装成功</h1>
                <div class="alert-success">
                    <h3>🎉 苹果CMS安装完成！</h3>
                    <div class="info-item"><div class="info-label">后台地址：</div><div class="info-value"><a href="admin.php">{$site_url}/admin.php</a></div></div>
                    <div class="info-item"><div class="info-label">管理员账号：</div><div class="info-value">{$config['admin_account']}</div></div>
                    <div class="info-item"><div class="info-label">管理员密码：</div><div class="info-value">{$config['admin_password']}</div></div>
                    <div class="info-item"><div class="info-label">数据库名称：</div><div class="info-value">{$config['database']}</div></div>
                    <div class="info-item"><div class="info-label">数据表前缀：</div><div class="info-value">{$config['prefix']}</div></div>
                </div>
                <div class="warning">
                    <h4>安全提示：</h4>
                    <ul>
                        <li>请及时修改默认管理员密码，以保证系统安全</li>
                        <li>建议删除或重命名安装文件，防止被恶意利用</li>
                        <li>定期备份数据库，以防数据丢失</li>
                    </ul>
                </div>
                <div style="margin-top: 30px;">
                    <a href="index.php" class="btn">访问首页</a>
                    <a href="admin.php" class="btn btn-secondary">进入后台</a>
                </div>
            </div>
        </body>
        </html>';
        
    } catch (PDOException $e) {
        echo '<!DOCTYPE html>
        <html lang="zh-CN">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>安装失败 - 苹果CMS</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; color: #333; }
                .container { max-width: 800px; margin: 50px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #dc3545; margin-bottom: 30px; text-align: center; }
                .alert-danger { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
                .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.3s; }
                .btn:hover { background-color: #0069d9; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>安装失败</h1>
                <div class="alert-danger">
                    <h3>数据库连接失败</h3>
                    <p>错误信息：" . $e->getMessage() . "</p>
                    <p>请检查数据库账号密码是否正确，以及MySQL服务是否启动</p>
                </div>
                <a href="one_click_install.php" class="btn">返回重试</a>
            </div>
        </body>
        </html>';
    }
} else {
    // 显示安装表单
    $php_version = PHP_VERSION;
    $mysql_available = extension_loaded('pdo_mysql') ? '√' : '×';
    $pdo_available = extension_loaded('pdo') ? '√' : '×';
    $fileinfo_available = extension_loaded('fileinfo') ? '√' : '×';
    $curl_available = extension_loaded('curl') ? '√' : '×';
    $gd_available = extension_loaded('gd') ? '√' : '×';
    
    $pdo_class = $pdo_available === '√' ? 'check-pass' : 'check-fail';
    $mysql_class = $mysql_available === '√' ? 'check-pass' : 'check-fail';
    $fileinfo_class = $fileinfo_available === '√' ? 'check-pass' : 'check-fail';
    $curl_class = $curl_available === '√' ? 'check-pass' : 'check-fail';
    $gd_class = $gd_available === '√' ? 'check-pass' : 'check-fail';
    
    echo '<!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>苹果CMS一键安装</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; color: #333; }
            .container { max-width: 800px; margin: 50px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #333; margin-bottom: 30px; text-align: center; }
            h2 { color: #555; margin-top: 30px; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
            .form-group { margin-bottom: 20px; }
            label { display: block; margin-bottom: 8px; font-weight: 500; color: #555; }
            input[type="text"], input[type="password"], select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; transition: border-color 0.3s; }
            input[type="text"]:focus, input[type="password"]:focus, select:focus { outline: none; border-color: #007bff; box-shadow: 0 0 0 2px rgba(0,123,255,0.25); }
            .btn { display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.3s; border: none; font-size: 16px; cursor: pointer; }
            .btn:hover { background-color: #0069d9; }
            .btn-block { width: 100%; margin-top: 30px; }
            .system-check { background-color: #f8f9fa; padding: 20px; border-radius: 4px; margin-bottom: 30px; }
            .check-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
            .check-item:last-child { border-bottom: none; }
            .check-pass { color: #28a745; }
            .check-fail { color: #dc3545; }
            .note { font-size: 14px; color: #6c757d; margin-top: 5px; font-style: italic; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>苹果CMS一键安装</h1>
            
            <h2>系统环境检测</h2>
            <div class="system-check">
                <div class="check-item"><span>PHP版本</span><span class="check-pass">" . $php_version . " (推荐7.4-8.5)</span></div>
                <div class="check-item"><span>PDO扩展</span><span class="