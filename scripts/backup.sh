#!/usr/bin/env bash
#
# 手动备份与恢复入口（日常用 `php artisan db:backup` 即可，本脚本用于运维例行任务）
#
# 用法：
#   ./scripts/backup.sh                    # 备份并在完成后列出台账
#   ./scripts/backup.sh --sync /mnt/nas    # 备份后同步到异地目录（NAS / 备份服务器）
#   ./scripts/backup.sh --restore storage/app/backups/xxx.sql.gz   # 从备份恢复

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

log() { echo -e "\033[36m[ERP]\033[0m $*"; }
die() { echo -e "\033[31m[错误]\033[0m $*" >&2; exit 1; }

SYNC_DIR=""
RESTORE_FILE=""

while [[ $# -gt 0 ]]; do
    case "$1" in
        --sync) SYNC_DIR="${2:-}"; shift 2 ;;
        --restore) RESTORE_FILE="${2:-}"; shift 2 ;;
        *) die "未知参数：$1" ;;
    esac
done

# ---------------------------------------------------------------------------
# 恢复：会覆盖现有数据，必须显式确认
# ---------------------------------------------------------------------------
if [[ -n "$RESTORE_FILE" ]]; then
    [[ -f "$RESTORE_FILE" ]] || die "备份文件不存在：$RESTORE_FILE"

    read -r -p "即将用 $RESTORE_FILE 覆盖当前数据库，请输入 yes 确认：" confirm
    [[ "$confirm" == "yes" ]] || die "已取消"

    log "开始恢复（请先执行 php artisan down 进入维护模式）"
    php artisan db:restore "$RESTORE_FILE" || die "恢复失败"

    log "恢复完成，建议立即执行：php artisan erp:health"
    exit 0
fi

# ---------------------------------------------------------------------------
# 备份
# ---------------------------------------------------------------------------
log "开始备份"
php artisan db:backup || die "备份失败"

if [[ -n "$SYNC_DIR" ]]; then
    [[ -d "$SYNC_DIR" ]] || die "异地目录不存在：$SYNC_DIR"
    log "同步到异地目录：$SYNC_DIR"
    rsync -av --delete storage/app/backups/ "$SYNC_DIR/" || die "异地同步失败"
fi

php artisan db:backup --list
