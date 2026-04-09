# 苹果CMS安装说明

## 系统要求
- PHP 7.4-8.5
- MySQL 5.7-8.0
- Apache/Nginx

## 安装方法

### 方法一：快速安装（推荐）
1. 将项目上传到服务器根目录
2. 访问 `http://你的域名/quick_install.php`
3. 填写数据库信息和管理员账号
4. 点击「开始安装」按钮
5. 安装完成后，删除 `quick_install.php` 文件

### 方法二：传统安装
1. 将项目上传到服务器根目录
2. 访问 `http://你的域名/install.php`
3. 按照安装向导步骤完成安装
4. 安装完成后，删除 `install.php` 文件

## 默认配置
- 默认数据库名称：`maccms10`
- 默认管理员账号：`admin`
- 默认管理员密码：`admin123`

## 安全提示
1. 安装完成后，请及时修改管理员密码
2. 删除安装文件（`quick_install.php` 或 `install.php`）
3. 定期备份数据库
4. 保持PHP版本和服务器软件的更新

## 常见问题

### 1. 数据库连接失败
- 检查数据库账号密码是否正确
- 检查MySQL服务是否启动
- 检查数据库用户是否有创建数据库的权限

### 2. 目录权限错误
- 确保以下目录有写入权限：
  - `application/`
  - `application/data/`
  - `application/runtime/`
  - `upload/`

### 3. PHP扩展缺失
- 确保安装了以下PHP扩展：
  - pdo
  - pdo_mysql
  - fileinfo
  - curl
  - gd

## 技术支持
- 作者：阿木
- 网址：Amu5.Com
- QQ：46552292
