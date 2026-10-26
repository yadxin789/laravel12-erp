# ERP 一键安装 / 初始化（Windows）
#
# 用法（管理员 PowerShell）：
#   .\scripts\install.ps1
#   .\scripts\install.ps1 -NoSeed          # 不导入演示数据
#   .\scripts\install.ps1 -NoDaemon        # 不注册计划任务
#
# 幂等：可重复执行。

param(
    [string]$PhpPath = "php.exe",
    [switch]$NoSeed,
    [switch]$NoDaemon
)

$ErrorActionPreference = "Stop"

$RootDir = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $RootDir

function Write-Step($message) { Write-Host "[ERP] $message" -ForegroundColor Cyan }
function Write-Warn($message) { Write-Host "[警告] $message" -ForegroundColor Yellow }

# ---------------------------------------------------------------------------
# 1. 环境检查
# ---------------------------------------------------------------------------
Write-Step "检查运行环境"

$phpCommand = Get-Command $PhpPath -ErrorAction SilentlyContinue
if (-not $phpCommand) { throw "未找到 PHP，请用 -PhpPath 指定 php.exe 路径" }
$phpExe = $phpCommand.Source

$phpVersion = & $phpExe -r "echo PHP_VERSION;"
Write-Step "PHP $phpVersion ($phpExe)"

if ([version]((& $phpExe -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")) -lt [version]"8.2") {
    throw "PHP 版本过低，需要 8.2 及以上"
}

if (-not (Get-Command "composer" -ErrorAction SilentlyContinue)) {
    throw "未安装 Composer：https://getcomposer.org/download/"
}

# ---------------------------------------------------------------------------
# 2. 依赖安装
# ---------------------------------------------------------------------------
Write-Step "安装 Composer 依赖"
composer install --no-interaction

# ---------------------------------------------------------------------------
# 3. 环境配置
# ---------------------------------------------------------------------------
if (-not (Test-Path ".env")) {
    Write-Step "生成 .env"
    Copy-Item ".env.example" ".env"
    & $phpExe artisan key:generate --ansi
} else {
    Write-Step ".env 已存在，跳过生成"
}

function Add-EnvDefault($key, $value) {
    $content = Get-Content ".env" -Raw
    if ($content -notmatch "(?m)^\s*$([regex]::Escape($key))=") {
        Add-Content ".env" "$key=$value"
    }
}
Add-EnvDefault "QUEUE_CONNECTION" "database"
Add-EnvDefault "DB_QUEUE_RETRY_AFTER" "180"
Add-EnvDefault "ERP_BACKUP_ENABLED" "true"
Add-EnvDefault "ERP_BACKUP_RETENTION_DAYS" "14"

# ---------------------------------------------------------------------------
# 4. 目录
# ---------------------------------------------------------------------------
Write-Step "准备存储目录"
@(
    "storage\app\public",
    "storage\app\backups",
    "storage\framework\cache",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs",
    "bootstrap\cache"
) | ForEach-Object {
    if (-not (Test-Path $_)) { New-Item -ItemType Directory -Path $_ -Force | Out-Null }
}

# ---------------------------------------------------------------------------
# 5. 数据库
# ---------------------------------------------------------------------------
Write-Step "执行数据库迁移"
& $phpExe artisan migrate --force --no-interaction

if ($NoSeed) {
    Write-Step "已跳过种子数据（-NoSeed）"
} else {
    Write-Step "导入种子数据"
    & $phpExe artisan db:seed --force --no-interaction
    if ($LASTEXITCODE -ne 0) { Write-Warn "种子数据导入失败（可忽略）" }
}

& $phpExe artisan storage:link --no-interaction
if ($LASTEXITCODE -ne 0) { Write-Warn "storage:link 失败，请确认 public/storage 是否已存在" }

# ---------------------------------------------------------------------------
# 6. 自启动与守护
# ---------------------------------------------------------------------------
if ($NoDaemon) {
    Write-Step "已跳过计划任务注册（-NoDaemon）"
} else {
    $taskScript = Join-Path $RootDir "deploy\windows\install-tasks.ps1"
    if (Test-Path $taskScript) {
        Write-Step "注册计划任务（调度 + 队列）"
        & $taskScript -ProjectRoot $RootDir -PhpPath $phpExe
    } else {
        Write-Warn "未找到 deploy\windows\install-tasks.ps1，跳过"
    }
}

# ---------------------------------------------------------------------------
# 7. 冒烟自检
# ---------------------------------------------------------------------------
Write-Step "执行健康检查"
& $phpExe artisan erp:health --no-alert
if ($LASTEXITCODE -ne 0) { Write-Warn "健康检查存在异常项，请查看上方输出" }

Write-Host ""
Write-Host "安装完成" -ForegroundColor Green
Write-Host "  调度清单：php artisan schedule:list"
Write-Host "  手动备份：php artisan db:backup"
Write-Host "  健康面板：/admin/system-health"
