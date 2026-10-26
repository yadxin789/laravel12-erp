#!/bin/sh
set -e

cd /var/www/html

# 挂载目录里的 storage 结构与权限（php-fpm 以 www-data 运行）
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views \
         storage/logs storage/app/public bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 等待数据库就绪
i=0
until php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: 3306).";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); } catch (Throwable $e) { exit(1); }' 2>/dev/null; do
    i=$((i+1))
    if [ "$i" -ge 60 ]; then
        echo "[entrypoint] database not reachable after 120s, giving up" >&2
        exit 1
    fi
    echo "[entrypoint] waiting for database... ($i)"
    sleep 2
done
echo "[entrypoint] database ready"

php artisan migrate --force

# 种子数据只跑一次（幂等）：DemoDataSeeder 基于首次创建，避免重启容器反复写库
if [ ! -f storage/app/.db_seeded ]; then
    php artisan db:seed --force
    mkdir -p storage/app && touch storage/app/.db_seeded
fi

php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[entrypoint] starting supervisor"
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/erp.ini
