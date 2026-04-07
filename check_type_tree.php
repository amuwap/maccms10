<?php
require_once 'thinkphp/start.php';
$typeModel = new app\common\model\Type();
$typeTree = $typeModel->getCache('type_tree');
print_r($typeTree);
?>