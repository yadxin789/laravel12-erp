#!/usr/bin/env bash
#
# 外部拨测脚本：供 Zabbix / Prometheus textfile / 监控宝 / 云监控等调用
#
# 退出码约定（便于监控系统判定）：
#   0  健康
#   1  存在警告项
#   2  存在异常项
#   3  站点不可达
#
# 用法：
#   ./scripts/health-check.sh                 # 检查 http://127.0.0.1/up
#   ./scripts/health-check.sh https://erp.example.com
#   ./scripts/health-check.sh - --json        # 只跑本地检查并输出 JSON

set -uo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

BASE_URL="${1:-http://127.0.0.1/up}"
OUTPUT="${2:-}"

# 1) 站点连通性
if [[ "$BASE_URL" != "-" ]]; then
    if command -v curl >/dev/null; then
        code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 10 "$BASE_URL" || echo 000)"
    else
        code="$(wget -q -O /dev/null --timeout=10 --server-response "$BASE_URL" 2>&1 | awk '/^  HTTP/{print $2}' | tail -n1)"
    fi
    if [[ "$code" != "200" ]]; then
        echo "UNREACHABLE: $BASE_URL (HTTP $code)"
        exit 3
    fi
fi

# 2) 业务与系统检查
if [[ "$OUTPUT" == "--json" ]]; then
    php artisan erp:health --json --no-alert
    exit $?
fi

php artisan erp:health --no-alert
exit $?
