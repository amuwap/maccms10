<?php
define('ROOT_PATH', __DIR__ . '/');
define('APP_PATH', __DIR__ . '/application/');
define('BIND_MODULE', 'install');
define('ENTRANCE', 'install');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/thinkphp/start.php';

use think\Db;

echo "开始手动安装（使用SQLite）...\n";

// 1. 创建数据库配置文件
echo "1. 创建数据库配置文件...\n";
$data = [
    'type' => 'sqlite',
    'database' => APP_PATH . 'data/maccms10.db',
    'prefix' => 'mac_'
];

$code = <<<INFO
<?php
return [
    'type'            => 'sqlite',
    'database'        => '{$data['database']}',
    'prefix'          => '{$data['prefix']}',
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
file_put_contents(APP_PATH.'database.php', $code);
echo "   数据库配置文件已创建\n";

// 2. 更新程序配置
echo "2. 更新程序配置...\n";
$config_new = include APP_PATH . 'extra/maccms.php';
$config_new['app']['cache_flag'] = substr(md5(time()),0,10);
$config_new['api']['vod']['status'] = 0;
$config_new['api']['art']['status'] = 0;
$config_new['interface']['status'] = 0;
$config_new['interface']['pass'] = substr(md5(time()),0,16);
$config_new['site']['install_dir'] = '/';

$res = file_put_contents(APP_PATH . 'extra/maccms.php', "<?php\nreturn " . var_export($config_new, true) . ";\n");
if ($res === false) {
    die("配置文件保存失败\n");
}
echo "   程序配置已更新\n";

// 3. 导入SQL文件
echo "3. 导入SQL文件...\n";
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
        $sql_list = mac_parse_sql($sql, 0, ['mac_' => $data['prefix']]);
        if ($sql_list) {
            $sql_list = array_filter($sql_list);
            $count = 0;
            foreach ($sql_list as $v) {
                try {
                    Db::execute($v);
                    $count++;
                } catch(\Exception $e) {
                    echo "      警告: " . $e->getMessage() . "\n";
                }
            }
            echo "      成功执行 $count 条语句\n";
        }
    }
}
echo "   SQL导入完成\n";

// 4. 创建管理员账号
echo "4. 创建管理员账号...\n";
$admin_data = [
    'admin_name' => 'admin',
    'admin_pwd' => 'admin123',
    'admin_status' => 1,
];

$adminModel = new \app\common\model\Admin();
$res = $adminModel->saveData($admin_data);
if ($res['code'] != 1) {
    die("管理员账号设置失败: " . $res['msg'] . "\n");
}
echo "   管理员账号创建成功: admin / admin123\n";

// 5. 创建安装锁文件
echo "5. 创建安装锁文件...\n";
if (!is_dir(APP_PATH.'data/install')) {
    mkdir(APP_PATH.'data/install', 0755, true);
}
file_put_contents(APP_PATH.'data/install/install.lock', date('Y-m-d H:i:s'));
echo "   安装锁文件已创建\n";

echo "\n安装完成！\n";
echo "后台地址: http://localhost:8889/admin.php\n";
echo "账号: admin\n";
echo "密码: admin123\n";
