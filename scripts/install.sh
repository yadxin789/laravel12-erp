#!/usr/bin/env bash
#
# ERP 一键安装 / 初始化（Linux & macOS）
#
# 用法：
#   ./scripts/install.sh                 # 完整安装（含依赖、迁移、种子数据）
#   ./scripts/install.sh --no-seed       # 不导入演示数据（生产环境推荐）
#   ./scripts/install.sh --no-daemon     # 不配置 supervisor / cron
#
# 幂等：可重复执行，已存在的配置会被覆盖为最新。

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

NO_SEED=0
NO_DAEMON=0
for arg in "$@"; do
    case "$arg" in
        --no-seed) NO_SEED=1 ;;
        --no-daemon) NO_DAEMON=1 ;;
        *) echo "未知参数：$arg"; exit 1 ;;
    esac
done

log()  { echo -e "\033[36m[ERP]\033[0m $*"; }
warn() { echo -e "\033[33m[警告]\033[0m $*"; }
die()  { echo -e "\033[31m[错误]\033[0m $*" >&2; exit 1; }

# ---------------------------------------------------------------------------
# 1. 环境检查
# ---------------------------------------------------------------------------
log "检查运行环境"
command -v php >/dev/null || die "未安装 PHP"

PHP_VERSION="$(php -r 'echo PHP_VERSION;')"
PHP_MAJOR_MINOR="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
if [[ "$(printf '%s\n' "8.2" "$PHP_MAJOR_MINOR" | sort -V | head -n1)" != "8.2" ]]; then
    die "PHP 版本过低（当前 $PHP_VERSION），需要 8.2 及以上"
fi
log "PHP $PHP_VERSION OK"

for ext in pdo_mysql mbstring openssl json curl zip gd; do
    php -m | grep -qi "^$ext$" || warn "缺少 PHP 扩展：$ext（部分功能可能不可用）"
done

if ! command -v composer >/dev/null; then
    die "未安装 Composer：https://getcomposer.org/download/"
fi

# ---------------------------------------------------------------------------
# 2. 依赖安装
# ---------------------------------------------------------------------------
log "安装 Composer 依赖"
if grep -q '"APP_ENV"\s*=\s*"production"' .env 2>/dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction
else
    composer install --optimize-autoloader --no-interaction
fi

# ---------------------------------------------------------------------------
# 3. 环境配置
# ---------------------------------------------------------------------------
if [[ ! -f .env ]]; then
    log "生成 .env"
    cp .env.example .env
    php artisan key:generate --ansi
else
    log ".env 已存在，跳过生成"
fi

# 队列与监控的关键默认值（不存在才写入，避免覆盖已有配置）
append_env() {
    local key="$1" value="$2"
    grep -qE "^\s*${key}=" .env || echo "${key}=${value}" >> .env
}
append_env "QUEUE_CONNECTION" "database"
append_env "DB_QUEUE_RETRY_AFTER" "180"
append_env "ERP_BACKUP_ENABLED" "true"
append_env "ERP_BACKUP_RETENTION_DAYS" "14"

# ---------------------------------------------------------------------------
# 4. 目录与权限
# ---------------------------------------------------------------------------
log "准备存储目录"
mkdir -p storage/app/public storage/app/backups \
         storage/framework/{cache,sessions,views} \
         storage/logs bootstrap/cache

if command -v chmod >/dev/null && [[ "$(id -u)" -eq 0 ]]; then
    # 生产环境若 Web 服务用户不是 root，请把 www-data 换成实际用户
    chown -R "${SUDO_USER:-www-data}:${SUDO_USER:-www-data}" storage bootstrap/cache 2>/dev/null || true
fi
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# ---------------------------------------------------------------------------
# 5. 数据库迁移与种子
# ---------------------------------------------------------------------------
log "执行数据库迁移"
php artisan migrate --force --no-interaction

if [[ "$NO_SEED" -eq 0 ]]; then
    log "导入种子数据"
    php artisan db:seed --force --no-interaction || warn "种子数据导入失败（可忽略，生产环境建议 --no-seed）"
else
    log "已跳过种子数据（--no-seed）"
fi

php artisan storage:link --no-interaction || warn "storage:link 失败，请确认 public/storage 是否已存在"

# ---------------------------------------------------------------------------
# 6. 生产优化
# ---------------------------------------------------------------------------
if grep -qE "^\s*APP_ENV=production" .env; then
    log "生产模式：缓存配置/路由/视图"
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# ---------------------------------------------------------------------------
# 7. 守护进程与定时任务
# ---------------------------------------------------------------------------
if [[ "$NO_DAEMON" -eq 1 ]]; then
    log "已跳过守护进程配置（--no-daemon）"
else
    if command -v supervisorctl >/dev/null; then
        log "配置 supervisor"
        sed "s#/var/www/laravel12-erp#${ROOT_DIR}#g" deploy/supervisor/erp-worker.conf > /tmp/erp-worker.conf
        sed "s#/var/www/laravel12-erp#${ROOT_DIR}#g" deploy/supervisor/erp-scheduler.conf > /tmp/erp-scheduler.conf
        install -m 644 /tmp/erp-worker.conf /etc/supervisor/conf.d/erp-worker.conf
        install -m 644 /tmp/erp-scheduler.conf /etc/supervisor/conf.d/erp-scheduler.conf
        supervisorctl reread && supervisorctl update
        supervisorctl restart erp-worker:*
        supervisorctl restart erp-scheduler:*
    elif command -v systemctl >/dev/null; then
        log "配置 systemd 服务"
        sed "s#/var/www/laravel12-erp#${ROOT_DIR}#g" deploy/systemd/erp-queue.service > /etc/systemd/system/erp-queue.service
        sed "s#/var/www/laravel12-erp#${ROOT_DIR}#g" deploy/systemd/erp-scheduler.service > /etc/systemd/system/erp-scheduler.service
        systemctl daemon-reload
        systemctl enable --now erp-queue erp-scheduler
    else
        warn "未检测到 supervisor / systemd，改用 cron"
        sed "s#/var/www/laravel12-erp#${ROOT_DIR}#g" deploy/cron/laravel-erp > /etc/cron.d/laravel-erp
        chmod 644 /etc/cron.d/laravel-erp
        warn "队列 Worker 需自行常驻：php artisan queue:work database --tries=3 --max-time=3600"
    fi
fi

# ---------------------------------------------------------------------------
# 8. 冒烟自检
# ---------------------------------------------------------------------------
log "执行健康检查"
php artisan erp:health --no-alert || warn "健康检查存在异常项，请查看上方输出"

cat <<EOF

\033[32m安装完成\033[0m

  项目目录：$ROOT_DIR
  调度清单：php artisan schedule:list
  队列状态：php artisan queue:monitor database:default
  手动备份：php artisan db:backup
  备份台账：php artisan db:backup --list
  健康面板：后台 → 系统监控（/admin/system-health）

EOF
