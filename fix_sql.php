<?php
echo "开始修复SQL文件...\n";

$files = [
    'application/install/sql/install.sql',
    'application/install/sql/extend.sql'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "处理文件: $file\n";
        $content = file_get_contents($file);
        
        // 修复AUTOINCREMENT为AUTO_INCREMENT
        $content = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $content);
        
        // 修复其他可能的SQLite语法
        $content = str_replace('"', '`', $content);
        
        file_put_contents($file, $content);
        echo "   修复完成\n";
    }
}

echo "SQL文件修复完成！\n";
