# 苹果CMS Docker镜像
# 作者：阿木
# 网址：Amu5.Com
# QQ：46552292

FROM php:7.4-apache

# 安装必要的PHP扩展
RUN apt-get update && apt-get install -y \
    mysql-client \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql fileinfo curl zip

# 启用Apache模块
RUN a2enmod rewrite

# 设置工作目录
WORKDIR /var/www/html

# 复制项目文件
COPY . /var/www/html/

# 设置权限
RUN chown -R www-data:www-data /var/www/html/ \
    && find /var/www/html/ -type d -exec chmod 755 {} \; \
    && find /var/www/html/ -type f -exec chmod 644 {} \;

# 暴露端口
EXPOSE 80

# 启动Apache
CMD ["apache2-foreground"]
