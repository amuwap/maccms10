#!/bin/bash

# 苹果CMS 10 一键安装脚本
# 作者：阿木
# 网址：Amu5.Com
# QQ：46552292
# 兼容：PHP 7.4-8.5，MySQL 5.7-8.0

echo "========================================"
echo "苹果CMS 10 一键安装脚本"
echo "========================================"
echo ""

# 检查PHP版本
echo "检查PHP版本..."
PHP_VERSION=$(php -v | grep -oP 'PHP \K[0-9]+\.[0-9]+')
if (( $(echo "$PHP_VERSION < 7.4" | bc -l) )); then
    echo "错误：PHP版本必须大于等于7.4"
    exit 1
fi

echo "PHP版本：$PHP_VERSION  ✓"

# 检查MySQL是否安装
echo "检查MySQL..."
if ! command -v mysql &> /dev/null; then
    echo "错误：MySQL未安装"
    exit 1
fi

echo "MySQL已安装  ✓"

# 检查Web服务器
echo "检查Web服务器..."
if command -v apache2 &> /dev/null; then
    WEB_SERVER="Apache"
elif command -v nginx &> /dev/null; then
    WEB_SERVER="Nginx"
else
    echo "错误：未检测到Apache或Nginx"
    exit 1
fi

echo "Web服务器：$WEB_SERVER  ✓"

# 检查目录权限
echo "检查目录权限..."
chmod -R 755 application/
chmod -R 755 runtime/
chmod -R 755 upload/

echo "目录权限设置完成  ✓"

# 删除安装锁文件
if [ -f application/data/install/install.lock ]; then
    echo "删除安装锁文件..."
    rm application/data/install/install.lock
    echo "安装锁文件已删除  ✓"
fi

echo ""
echo "========================================"
echo "环境检查完成，所有要求均已满足！"
echo "========================================"
echo ""
echo "请在浏览器中访问以下地址开始安装："
echo "http://$(hostname -I | awk '{print $1}')/install.php"
echo ""
echo "安装步骤："
echo "1. 同意用户协议"
echo "2. 环境检测（自动通过）"
echo "3. 填写数据库信息"
echo "4. 设置管理员账号密码"
echo "5. 点击安装完成"
echo ""
echo "========================================"
echo "安装完成后，请记得修改后台入口文件名以提高安全性！"
echo "例如：将 admin.php 重命名为 admin_xxx.php"
echo "========================================"
