<?php
// 测试Type模型的getCache方法
require_once 'thinkphp/start.php';

// 清除缓存
Cache::rm('type_list');
Cache::rm('type_tree');

// 获取type_tree
$typeModel = new app\common\model\Type();
try {
    $typeTree = $typeModel->getCache('type_tree');
    echo "Type tree retrieved successfully!\n";
    print_r($typeTree);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>