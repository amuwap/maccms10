<?php
// 测试模板解析，定位api常量未定义的错误

// 首先定义api常量
define('api', 'api');
echo 'api常量已定义: ' . api . '\n';

// 模拟配置
$config = [
    'upload' => [
        'mode' => 'local',
        'api' => [
            'weibo' => [
                'user' => '',
                'pwd' => '',
                'size' => 'large',
                'cookie' => '',
                'time' => time()
            ]
        ]
    ]
];

// 模拟模板变量
extract(['config' => $config]);

// 测试加载微博上传模板
$weibo_template = file_get_contents('./application/admin/view/extend/upload/weibo.html');
echo '加载微博上传模板成功\n';

// 简单解析模板
echo '解析模板...\n';
// 替换模板变量
$weibo_template = str_replace('{$config["upload"]["api"]["weibo"]["user"]}', $config['upload']['api']['weibo']['user'], $weibo_template);
$weibo_template = str_replace('{$config["upload"]["api"]["weibo"]["pwd"]}', $config['upload']['api']['weibo']['pwd'], $weibo_template);
$weibo_template = str_replace('{$config["upload"]["api"]["weibo"]["cookie"]}', $config['upload']['api']['weibo']['cookie'], $weibo_template);
$weibo_template = str_replace('{$config["upload"]["api"]["weibo"]["time"]}', $config['upload']['api']['weibo']['time'], $weibo_template);

// 测试条件判断
echo '测试条件判断...\n';
if (preg_match('/\{if condition="\$config\[\'upload\'\]\[api\]\[weibo\]\[size\] eq \'large\'\}/', $weibo_template)) {
    echo '发现直接使用api常量的代码！\n';
}

// 显示模板内容
echo '模板内容中包含api的部分:\n';
preg_match_all('/\$config\[.*?api.*?\]/', $weibo_template, $matches);
foreach ($matches[0] as $match) {
    echo $match . '\n';
}

echo '测试完成！\n';
