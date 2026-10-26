FROM php:8.4-fpm-alpine

# 系统依赖：时区、 supervisor（容器内常驻进程管理）、PHP 扩展编译依赖
# 国内网络环境：apk 源切换到阿里云镜像
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories \
    && apk add --no-cache \
        tzdata \
        supervisor \
        icu-dev \
        libzip-dev \
        libpng-dev \
        freetype-dev \
        libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql bcmath pcntl zip intl gd opcache \
    && cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime \
    && echo "Asia/Shanghai" > /etc/timezone \
    && rm -rf /var/cache/apk/*

# 容器内 PHP 运行参数（与挂载的 99-erp.ini 二选一，这里用挂载便于调整）
# COPY php-erp.ini /usr/local/etc/php/conf.d/99-erp.ini

COPY supervisord-erp.ini /etc/supervisor/conf.d/erp.ini
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh \
    && mkdir -p /var/www/html/storage/framework/{cache/data,sessions,views} \
    && mkdir -p /var/www/html/storage/logs \
    && chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

ENTRYPOINT ["/entrypoint.sh"]
