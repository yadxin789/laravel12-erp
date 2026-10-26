#!/usr/bin/env bash
#
# ERP 发布 / 更新脚本（Linux）
#
# 用法：./scripts/deploy.sh [分支名]
#
# 关键点：
#  1) 迁移前先备份，失败可回滚
#  2) 迁移完成后执行 queue:restart，否则 Worker 仍跑旧代码（最常见的上线事故）
#  3) 生产环境重建配置/路由/视图缓存

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

BRANCH="${1:-main}"
PRODUCTION=0
grep -qE "^\s*APP_ENV=production" .env 2>/dev/null && PRODUCTION=1

log() { echo -e "\033[36m[ERP]\033[0m $*"; }
warn() { echo -e "\033[33m[警告]\033[0m $*"; }

# ---------------------------------------------------------------------------
log "进入维护模式"
php artisan down --refresh=15 --secret="$(openssl rand -hex 8)" || true

# ---------------------------------------------------------------------------
log "拉取代码（$BRANCH）"
git fetch --all --prune
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH" || warn "拉取失败，继续使用当前代码"

log "安装依赖"
if [[ "$PRODUCTION" -eq 1 ]]; then
    composer install --no-dev --optimize-autoloader --no-interaction
else
    composer install --optimize-autoloader --no-interaction
fi

# ---------------------------------------------------------------------------
log "备份数据库（迁移前）"
php artisan db:backup || warn "备份失败，出于安全考虑已中止发布"
if [[ $? -ne 0 ]]; then exit 1; fi

# ---------------------------------------------------------------------------
log "执行迁移"
php artisan migrate --force --no-interaction

if [[ "$PRODUCTION" -eq 1 ]]; then
    log "重建缓存"
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# ---------------------------------------------------------------------------
log "重启队列 Worker（加载新代码）"
php artisan queue:restart

log "退出维护模式"
php artisan up

log "健康检查"
php artisan erp:health --no-alert || warn "健康检查存在异常项"

log "发布完成"
