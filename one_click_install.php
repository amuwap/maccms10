<?php
define('ROOT_PATH', __DIR__ . '/');
define('APP_PATH', __DIR__ . '/application/');
define('BIND_MODULE', 'install');
define('ENTRANCE', 'install');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/thinkphp/start.php';

use think\Db;

echo "开始一键安装（使用MySQL）...\n";

echo "=======================================\n";
echo "苹果CMS 10 一键安装脚本\n";
echo "=======================================\n";

// 1. 创建数据库配置文件
echo "1. 创建数据库配置文件...\n";
$db_config = [
    'type' => 'mysql',
    'hostname' => '127.0.0.1',
    'hostport' => '3306',
    'database' => 'maccms10',
    'username' => 'root',
    'password' => '',
    'prefix' => 'mac_'
];

$code = <<<INFO
<?php
return [
    'type'            => 'mysql',
    'hostname'        => '{$db_config['hostname']}',
    'database'        => '{$db_config['database']}',
    'username'        => '{$db_config['username']}',
    'password'        => '{$db_config['password']}',
    'hostport'        => '{$db_config['hostport']}',
    'dsn'             => '',
    'params'          => [],
    'charset'         => 'utf8mb4',
    'prefix'          => '{$db_config['prefix']}',
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
    'query'           => '\\\\think\\\\db\\\\Query',
];
INFO;

// 确保database.php文件存在且可写
if (!file_exists(APP_PATH.'database.php')) {
    touch(APP_PATH.'database.php');
}
chmod(APP_PATH.'database.php', 0666);
file_put_contents(APP_PATH.'database.php', $code);
echo "   ✅ 数据库配置文件已创建\n";

// 2. 测试数据库连接
echo "2. 测试数据库连接...\n";
try {
    $db = Db::connect();
    // 选择数据库
    $db->execute("USE `{$db_config['database']}`");
    $version = $db->query('SELECT version()')[0]['version()'];
    echo "   ✅ 数据库连接成功！MySQL版本: $version\n";
    echo "   ✅ 已选择数据库: {$db_config['database']}\n";
} catch (\Exception $e) {
    die("   ❌ 数据库连接失败: " . $e->getMessage() . "\n");
}

// 3. 清空数据库表
echo "3. 准备数据库...\n";
try {
    $tables = $db->query("SHOW TABLES LIKE '{$db_config['prefix']}%'");
    foreach ($tables as $table) {
        $table_name = reset($table);
        $db->execute("DROP TABLE IF EXISTS `$table_name`");
    }
    echo "   ✅ 数据库表已清理\n";
} catch (\Exception $e) {
    echo "   ⚠️  清理表时出错: " . $e->getMessage() . "\n";
}

// 4. 导入SQL文件
echo "4. 导入SQL文件...\n";
require_once APP_PATH . 'common.php';

$sql_files = [
    APP_PATH.'install/sql/install.sql',
    APP_PATH.'install/sql/extend.sql',
    APP_PATH.'install/sql/new_types.sql'
];

foreach ($sql_files as $sql_file) {
    if (file_exists($sql_file)) {
        echo "   导入: " . basename($sql_file) . "\n";
        $sql = file_get_contents($sql_file);
        $sql_list = mac_parse_sql($sql, 0, ['mac_' => $db_config['prefix']]);
        if ($sql_list) {
            $sql_list = array_filter($sql_list);
            $count = 0;
            foreach ($sql_list as $v) {
                try {
                    $db->execute($v);
                    $count++;
                } catch(\Exception $e) {
                    echo "      ⚠️  执行失败: " . $e->getMessage() . "\n";
                }
            }
            echo "      ✅ 成功执行 $count 条语句\n";
        }
    }
}
echo "   ✅ SQL导入完成\n";

// 5. 更新程序配置
echo "5. 更新程序配置...\n";
$config_new = include APP_PATH . 'extra/maccms.php';
$config_new['app']['cache_flag'] = substr(md5(time()),0,10);
$config_new['api']['vod']['status'] = 0;
$config_new['api']['art']['status'] = 0;
$config_new['interface']['status'] = 0;
$config_new['interface']['pass'] = substr(md5(time()),0,16);
$config_new['site']['install_dir'] = '/';

$res = file_put_contents(APP_PATH . 'extra/maccms.php', "<?php\nreturn " . var_export($config_new, true) . ";\n");
if ($res === false) {
    die("   ❌ 配置文件保存失败\n");
}
echo "   ✅ 程序配置已更新\n";

// 6. 创建管理员账号
echo "6. 创建管理员账号...\n";
$admin_data = [
    'admin_name' => 'admin',
    'admin_pwd' => 'admin123',
    'admin_status' => 1,
];

try {
    $db->execute("INSERT INTO `{$db_config['prefix']}admin` (`admin_name`, `admin_pwd`, `admin_status`, `admin_auth`, `admin_login_time`, `admin_last_login_time`) VALUES (?, ?, ?, ?, ?, ?)", [
        $admin_data['admin_name'],
        md5($admin_data['admin_pwd']),
        $admin_data['admin_status'],
        '',
        time(),
        time()
    ]);
    echo "   ✅ 管理员账号创建成功: admin / admin123\n";
} catch (\Exception $e) {
    die("   ❌ 管理员账号创建失败: " . $e->getMessage() . "\n");
}

// 7. 创建安装锁文件
echo "7. 创建安装锁文件...\n";
if (!is_dir(APP_PATH.'data/install')) {
    mkdir(APP_PATH.'data/install', 0755, true);
}
file_put_contents(APP_PATH.'data/install/install.lock', date('Y-m-d H:i:s'));
echo "   ✅ 安装锁文件已创建\n";

echo "\n=======================================\n";
echo "🎉 安装完成！\n";
echo "=======================================\n";
echo "后台地址: http://localhost:8889/admin.php\n";
echo "账号: admin\n";
echo "密码: admin123\n";
echo "=======================================\n";
