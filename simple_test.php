<?php
// 简单测试脚本，直接连接数据库并生成type_tree

// 数据库配置
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'maccms10';

// 连接数据库
$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_error) {
    die('连接失败: ' . $mysqli->connect_error);
}

// 查询分类数据
$query = "SELECT type_id, type_name, type_mid, type_pid FROM mac_type ORDER BY type_id asc";
$result = $mysqli->query($query);

$list = [];
while ($row = $result->fetch_assoc()) {
    $list[$row['type_id']] = $row;
}

// 生成树结构
function mac_list_to_tree($list, $pk='id',$pid = 'pid',$child = 'child',$root=0)
{
    $tree = array();
    if(is_array($list)) {
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }

        foreach ($list as $key => $data) {
            $parentId = $data[$pid];

            if ($root == $parentId) {
                $tree[] =& $list[$key];

            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

$type_tree = mac_list_to_tree($list, 'type_id', 'type_pid', 'child', 0);
print_r($type_tree);

// 关闭数据库连接
$mysqli->close();
?>