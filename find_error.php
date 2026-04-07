<?php
// 逐步包含文件以定位错误

echo "Step 1: Including thinkphp/base.php...\n";
include __DIR__ . '/thinkphp/base.php';
echo "Success\n";

echo "Step 2: Including application/common.php...\n";
include __DIR__ . '/application/common.php';
echo "Success\n";

echo "Step 3: Including application/config.php...\n";
include __DIR__ . '/application/config.php';
echo "Success\n";

echo "Step 4: Including application/database.php...\n";
include __DIR__ . '/application/database.php';
echo "Success\n";

echo "Step 5: Including application/route.php...\n";
include __DIR__ . '/application/route.php';
echo "Success\n";

echo "Step 6: Including application/tags.php...\n";
include __DIR__ . '/application/tags.php';
echo "Success\n";

echo "All files included successfully!\n";
?>