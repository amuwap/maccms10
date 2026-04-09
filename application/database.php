<?php
/**
 * 数据库配置文件
 * 作者：阿木
 * 网址：Amu5.Com
 * QQ：46552292
 * 请根据您的实际数据库信息修改以下配置
 */
return [
    'type'            => 'mysql',
    'hostname'        => '127.0.0.1',  // 数据库服务器地址
    'database'        => 'maccms10',     // 数据库名称
    'username'        => 'root',          // 数据库用户名
    'password'        => '',              // 数据库密码（请填入您的实际密码）
    'hostport'        => '3306',          // 数据库端口
    'dsn'             => '',
    'params'          => [],
    'charset'         => 'utf8mb4',       // 字符集（支持emoji）
    'prefix'          => 'mac_',          // 表前缀
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
    'query'           => '\think\db\Query',
];