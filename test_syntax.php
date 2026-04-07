<?php
// 测试多个文件的语法错误

$files = [
    '/workspace/index.php',
    '/workspace/admin.php',
    '/workspace/thinkphp/start.php',
    '/workspace/thinkphp/base.php',
    '/workspace/application/common.php',
    '/workspace/application/admin/controller/Base.php',
    '/workspace/application/admin/controller/Index.php',
    '/workspace/thinkphp/library/think/App.php',
    '/workspace/thinkphp/library/think/Request.php'
];

foreach ($files as $file) {
    echo "Checking $file... ";
    $output = [];
    $return_var = 0;
    exec("php -l $file", $output, $return_var);
    if ($return_var === 0) {
        echo "OK\n";
    } else {
        echo "ERROR\n";
        foreach ($output as $line) {
            echo "  $line\n";
        }
    }
}
?>