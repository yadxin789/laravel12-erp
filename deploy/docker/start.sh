#!/bin/bash
# Dedao ERP 容器编排（兼容 openEuler 仓库自带的旧版 Docker 18.09，不依赖 compose）
# 用法：/opt/erp/start.sh   —— 幂等，可重复执行（升级代码后先重新 build）
set -e

# 数据库口令不入库：从密钥文件读取（默认 /opt/erp/secrets/db.env，权限 600）
SECRETS_FILE=${ERP_SECRETS_FILE:-/opt/erp/secrets/db.env}
if [ ! -f "$SECRETS_FILE" ]; then
    echo "缺少密钥文件：$SECRETS_FILE（需包含 DB_USER_PASSWORD / DB_ROOT_PASSWORD，权限 600，不纳入版本库）" >&2
    exit 1
fi
# shellcheck disable=SC1090
. "$SECRETS_FILE"

# 应用密钥同样不入库：/opt/erp/app/.env 由 deploy/docker/env.production.example 生成本地副本
if [ ! -f /opt/erp/app/.env ]; then
    echo "缺少 /opt/erp/app/.env（请复制 env.production.example 后填入 APP_KEY / DB_PASSWORD）" >&2
    exit 1
fi

cd /opt/erp

docker network inspect erp-net >/dev/null 2>&1 || docker network create erp-net

# ---------- 自签 TLS 证书（https://192.168.2.102） ----------
mkdir -p certs
if [ ! -f certs/erp.crt ]; then
    openssl req -x509 -nodes -newkey rsa:2048 -days 3650 \
        -keyout certs/erp.key -out certs/erp.crt \
        -subj "/CN=192.168.2.102" \
        -addext "subjectAltName=IP:192.168.2.102" >/dev/null 2>&1
    chmod 600 certs/erp.key
fi

# ---------- 数据库 ----------
docker rm -f erp-db >/dev/null 2>&1 || true
docker run -d --name erp-db \
    --network erp-net --restart unless-stopped \
    -e MYSQL_DATABASE=dedao_erp \
    -e MYSQL_USER=erp \
    -e MYSQL_PASSWORD=$DB_USER_PASSWORD \
    -e MYSQL_ROOT_PASSWORD=$DB_ROOT_PASSWORD \
    -v erp-db-data:/var/lib/mysql \
    --health-cmd="mysqladmin ping -p$DB_ROOT_PASSWORD --silent" \
    --health-interval=10s --health-timeout=5s --health-retries=12 \
    mysql:8.0

# ---------- 应用（php-fpm 8.4 + 队列 worker + 调度器） ----------
docker build -t erp-php:8.4 /opt/erp/docker
docker rm -f erp-app >/dev/null 2>&1 || true
docker run -d --name erp-app \
    --network erp-net --restart unless-stopped \
    -v /opt/erp/app:/var/www/html \
    -v /opt/erp/docker/php-erp.ini:/usr/local/etc/php/conf.d/99-erp.ini:ro \
    --env-file /opt/erp/app/.env \
    erp-php:8.4

# ---------- Web（nginx，80 -> 443） ----------
docker rm -f erp-web >/dev/null 2>&1 || true
docker run -d --name erp-web \
    --network erp-net --restart unless-stopped \
    -p 80:80 -p 443:443 \
    -v /opt/erp/app/public:/var/www/html/public:ro \
    -v /opt/erp/docker/nginx-erp.conf:/etc/nginx/conf.d/default.conf:ro \
    -v /opt/erp/certs:/etc/nginx/certs:ro \
    nginx:alpine

echo "containers:"
docker ps --filter name=erp- --format 'table {{.Names}}\t{{.Status}}\t{{.Ports}}'
