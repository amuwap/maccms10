<?php
/**
 * 苹果CMS自动一键安装脚本
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
    echo '<h1>系统已安装</h1>';
    echo '<p>如需重新安装，请删除 ' . APP_PATH . 'data/install/install.lock 文件</p>';
    echo '<p><a href="index.php">访问首页</a> | <a href="admin.php">进入后台</a></p>';
    exit;
}

// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';

// 自动安装配置
$config = [
    'hostname' => '127.0.0.1',
    'hostport' => '3306',
    'database' => 'maccms10',
    'username' => 'root',
    'password' => '',
    'prefix' => 'mac_',
    'cover' => 1,
    'admin_account' => 'admin',
    'admin_password' => 'admin123'
];

// 环境检测
function checkEnvironment() {
    $errors = [];
    
    // 检查PHP版本
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        $errors[] = 'PHP版本必须大于等于7.4';
    }
    
    // 检查必要的PHP扩展
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
        APP_PATH . 'runtime/',
        APP_PATH . 'upload/'
    ];
    
    foreach ($required_dirs as $dir) {
        if (!is_writable($dir)) {
            $errors[] = '目录不可写：' . $dir;
        }
    }
    
    return $errors;
}

// 连接数据库
function connectDatabase($config) {
    try {
        $dsn = "mysql:host={$config['hostname']};port={$config['hostport']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        return ['error' => $e->getMessage()];
    }
}

// 创建数据库
function createDatabase($pdo, $database) {
    try {
        $sql = "CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        return ['error' => $e->getMessage()];
    }
}

// 导入SQL文件
function importSql($pdo, $database, $prefix) {
    $sql_files = [
        APP_PATH . 'install/sql/install.sql',
        APP_PATH . 'install/sql/extend.sql',
        APP_PATH . 'install/sql/new_types.sql'
    ];
    
    try {
        // 选择数据库
        $pdo->exec("USE `{$database}`");
        
        foreach ($sql_files as $sql_file) {
            if (file_exists($sql_file)) {
                $sql = file_get_contents($sql_file);
                // 替换表前缀
                $sql = str_replace('mac_', $prefix, $sql);
                // 执行SQL
                $pdo->exec($sql);
            }
        }
        return true;
    } catch (PDOException $e) {
        return ['error' => $e->getMessage()];
    }
}

// 创建管理员账号
function createAdmin($pdo, $database, $prefix, $account, $password) {
    try {
        $pdo->exec("USE `{$database}`");
        
        // 生成密码哈希
        $password_hash = md5($password);
        
        // 插入管理员账号
        $sql = "INSERT INTO `{$prefix}admin` (`admin_name`, `admin_pwd`, `admin_status`) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$account, $password_hash, 1]);
        
        return true;
    } catch (PDOException $e) {
        return ['error' => $e->getMessage()];
    }
}

// 生成数据库配置文件
function generateDatabaseConfig($config) {
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
    // 数据库类型
    'type'            => 'mysql',
    // 服务器地址
    'hostname'        => '{$config['hostname']}',
    // 数据库名
    'database'        => '{$config['database']}',
    // 用户名
    'username'        => '{$config['username']}',
    // 密码
    'password'        => '{$config['password']}',
    // 端口
    'hostport'        => '{$config['hostport']}',
    // 连接dsn
    'dsn'             => '',
    // 数据库连接参数
    'params'          => [],
    // 数据库编码默认采用utf8mb4（支持emoji）
    'charset'         => 'utf8mb4',
    // 数据库表前缀
    'prefix'          => '{$config['prefix']}',
    // 数据库调试模式
    'debug'           => false,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'          => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate'     => false,
    // 读写分离后 主服务器数量
    'master_num'      => 1,
    // 指定从服务器序号
    'slave_no'        => '',
    // 是否严格检查字段是否存在
    'fields_strict'   => false,
    // 数据集返回类型
    'resultset_type'  => 'array',
    // 自动写入时间戳字段
    'auto_timestamp'  => false,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 是否需要进行SQL性能分析
    'sql_explain'     => false,
    // Builder类
    'builder'         => '',
    // Query类
    'query'           => '\\think\\db\\Query',
];
INFO;
    
    file_put_contents(APP_PATH . 'database.php', $code);
    return true;
}

// 生成安装锁文件
function createInstallLock() {
    file_put_contents(APP_PATH . 'data/install/install.lock', date('Y-m-d H:i:s'));
    return true;
}

// 主安装函数
function install($config) {
    echo '<h1>苹果CMS一键安装</h1>';
    echo '<div style="max-width: 800px; margin: 0 auto;">';
    
    // 1. 环境检测
    echo '<h2>1. 环境检测</h2>';
    $env_errors = checkEnvironment();
    if (!empty($env_errors)) {
        echo '<div style="color: red;">';
        foreach ($env_errors as $error) {
            echo '<p>❌ ' . $error . '</p>';
        }
        echo '</div>';
        return false;
    }
    echo '<p>✅ 环境检测通过</p>';
    
    // 2. 连接数据库
    echo '<h2>2. 数据库连接</h2>';
    $pdo = connectDatabase($config);
    if (isset($pdo['error'])) {
        echo '<div style="color: red;">';
        echo '<p>❌ 数据库连接失败：' . $pdo['error'] . '</p>';
        echo '<p>请检查数据库账号密码是否正确，以及MySQL服务是否启动</p>';
        echo '</div>';
        return false;
    }
    echo '<p>✅ 数据库连接成功</p>';
    
    // 3. 创建数据库
    echo '<h2>3. 创建数据库</h2>';
    $create_result = createDatabase($pdo, $config['database']);
    if (isset($create_result['error'])) {
        echo '<div style="color: red;">';
        echo '<p>❌ 数据库创建失败：' . $create_result['error'] . '</p>';
        echo '</div>';
        return false;
    }
    echo '<p>✅ 数据库创建成功</p>';
    
    // 4. 导入SQL
    echo '<h2>4. 导入SQL文件</h2>';
    $import_result = importSql($pdo, $config['database'], $config['prefix']);
    if (isset($import_result['error'])) {
        echo '<div style="color: red;">';
        echo '<p>❌ SQL导入失败：' . $import_result['error'] . '</p>';
        echo '</div>';
        return false;
    }
    echo '<p>✅ SQL导入成功</p>';
    
    // 5. 创建管理员账号
    echo '<h2>5. 创建管理员账号</h2>';
    $admin_result = createAdmin($pdo, $config['database'], $config['prefix'], $config['admin_account'], $config['admin_password']);
    if (isset($admin_result['error'])) {
        echo '<div style="color: red;">';
        echo '<p>❌ 管理员账号创建失败：' . $admin_result['error'] . '</p>';
        echo '</div>';
        return false;
    }
    echo '<p>✅ 管理员账号创建成功</p>';
    
    // 6. 生成配置文件
    echo '<h2>6. 生成配置文件</h2>';
    generateDatabaseConfig($config);
    echo '<p>✅ 配置文件生成成功</p>';
    
    // 7. 创建安装锁
    echo '<h2>7. 完成安装</h2>';
    createInstallLock();
    echo '<p>✅ 安装完成</p>';
    
    // 8. 显示安装结果
    echo '<h2>安装结果</h2>';
    echo '<div style="background: #f0f8ff; padding: 20px; border-radius: 5px;">';
    echo '<p>🎉 苹果CMS安装成功！</p>';
    echo '<p><strong>后台地址：</strong><a href="admin.php">' . $_SERVER['HTTP_HOST'] . '/admin.php</a></p>';
    echo '<p><strong>管理员账号：</strong>' . $config['admin_account'] . '</p>';
    echo '<p><strong>管理员密码：</strong>' . $config['admin_password'] . '</p>';
    echo '<p><strong>数据库名称：</strong>' . $config['database'] . '</p>';
    echo '<p><strong>数据库前缀：</strong>' . $config['prefix'] . '</p>';
    echo '<p>请及时修改默认密码，以保证系统安全！</p>';
    echo '</div>';
    
    echo '</div>';
    return true;
}

// 执行安装
install($config);
