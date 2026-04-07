<?php
require_once 'thinkphp/start.php';

// 清除缓存
Cache::rm('type_list');
Cache::rm('type_tree');

// 获取type_list
$typeModel = new app\common\model\Type();
$typeList = $typeModel->listData([],'type_id asc');
print_r($typeList['list']);

// 生成type_tree
$typeTree = mac_list_to_tree($typeList['list'], 'type_id', 'type_pid', 'child', 0);
print_r($typeTree);
?>