# ERP 发布 / 更新脚本（Windows）
#
# 用法：.\scripts\deploy.ps1 [-Branch main]

param(
    [string]$PhpPath = "php.exe",
    [string]$Branch = "main"
)

$ErrorActionPreference = "Stop"

$RootDir = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $RootDir

function Write-Step($message) { Write-Host "[ERP] $message" -ForegroundColor Cyan }
function Write-Warn($message) { Write-Host "[警告] $message" -ForegroundColor Yellow }

$phpExe = (Get-Command $PhpPath -ErrorAction SilentlyContinue).Source
if (-not $phpExe) { throw "未找到 PHP，请用 -PhpPath 指定" }

$envContent = if (Test-Path ".env") { Get-Content ".env" -Raw } else { "" }
$isProduction = $envContent -match "(?m)^\s*APP_ENV=production"

Write-Step "进入维护模式"
& $phpExe artisan down --refresh=15

Write-Step "拉取代码（$Branch）"
git fetch --all --prune
git checkout $Branch
git pull --ff-only origin $Branch
if ($LASTEXITCODE -ne 0) { Write-Warn "拉取失败，继续使用当前代码" }

Write-Step "安装依赖"
if ($isProduction) {
    composer install --no-dev --optimize-autoloader --no-interaction
} else {
    composer install --optimize-autoloader --no-interaction
}

Write-Step "备份数据库（迁移前）"
& $phpExe artisan db:backup
if ($LASTEXITCODE -ne 0) { throw "备份失败，已中止发布" }

Write-Step "执行迁移"
& $phpExe artisan migrate --force --no-interaction

if ($isProduction) {
    Write-Step "重建缓存"
    & $phpExe artisan config:cache
    & $phpExe artisan route:cache
    & $phpExe artisan view:cache
}

Write-Step "重启队列 Worker（加载新代码）"
& $phpExe artisan queue:restart

Write-Step "退出维护模式"
& $phpExe artisan up

Write-Step "健康检查"
& $phpExe artisan erp:health --no-alert
if ($LASTEXITCODE -ne 0) { Write-Warn "健康检查存在异常项" }

Write-Step "发布完成"
