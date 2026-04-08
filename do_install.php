<?php
define('ROOT_PATH', __DIR__ . '/');
define('APP_PATH', __DIR__ . '/application/');
define('BIND_MODULE', 'install');
define('ENTRANCE', 'install');

require __DIR__ . '/thinkphp/start.php';

use think\Db;

echo "开始安装...\n";

// 1. 创建数据库配置文件
$data = [
    'type' => 'mysql',
    'hostname' => '127.0.0.1',
    'database' => 'maccms10',
    'username' => 'root',
    'password' => '',
    'hostport' => '3306',
    'prefix' => 'mac_'
];

echo "生成数据库配置文件...\n";
$code = <<<INFO
<?php
return [
    'type'            => 'mysql',
    'hostname'        => '127.0.0.1',
    'database'        => 'maccms10',
    'username'        => 'root',
    'password'        => '',
    'hostport'        => '3306',
    'dsn'             => '',
    'params'          => [],
    'charset'         => 'utf8mb4',
    'prefix'          => 'mac_',
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
file_put_contents(APP_PATH.'database.php', $code);
echo "数据库配置文件已创建\n";

// 2. 确保数据目录存在
if (!is_dir(APP_PATH . 'data')) {
    mkdir(APP_PATH . 'data', 0755, true);
}

// 3. 更新程序配置
$config_new = include APP_PATH . 'extra/maccms.php';
$config_new['app']['cache_flag'] = substr(md5(time()),0,10);
$config_new['api']['vod']['status'] = 0;
$config_new['api']['art']['status'] = 0;
$config_new['interface']['status'] = 0;
$config_new['interface']['pass'] = substr(md5(time()),0,16);
$config_new['site']['install_dir'] = '/';

echo "更新程序配置...\n";
$res = file_put_contents(APP_PATH . 'extra/maccms.php', "<?php\nreturn " . var_export($config_new, true) . ";\n");
if ($res === false) {
    die("配置文件保存失败\n");
}
echo "程序配置已更新\n";

// 4. 导入SQL文件
$sql_files = [
    APP_PATH.'install/sql/install.sql',
    APP_PATH.'install/sql/extend.sql',
    APP_PATH.'install/sql/new_types.sql'
];

echo "导入SQL文件...\n";
require_once APP_PATH . 'common.php';

foreach ($sql_files as $sql_file) {
    if (file_exists($sql_file)) {
        echo "导入: " . basename($sql_file) . "\n";
        $sql = file_get_contents($sql_file);
        $sql_list = mac_parse_sql($sql, 0, ['mac_' => 'mac_']);
        if ($sql_list) {
            $sql_list = array_filter($sql_list);
            foreach ($sql_list as $v) {
                try {
                    Db::execute($v);
                } catch(\Exception $e) {
                    echo "警告: " . $e->getMessage() . "\n";
                }
            }
        }
    }
}
echo "SQL导入完成\n";

// 5. 创建管理员账号
echo "创建管理员账号...\n";
$data = [
    'admin_name' => 'admin',
    'admin_pwd' => 'admin123',
    'admin_status' => 1,
];

$adminModel = new \app\common\model\Admin();
$res = $adminModel->saveData($data);
if ($res['code'] != 1) {
    die("管理员账号设置失败: " . $res['msg'] . "\n");
}
echo "管理员账号创建成功: admin / admin123\n";

// 6. 创建安装锁文件
file_put_contents(APP_PATH.'data/install/install.lock', date('Y-m-d H:i:s'));

echo "\n安装完成！\n";
echo "后台地址: http://localhost:8889/admin.php\n";
echo "账号: admin\n";
echo "密码: admin123\n";
