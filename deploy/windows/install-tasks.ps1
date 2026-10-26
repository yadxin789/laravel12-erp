# ERP Windows 自启动配置
#
# 用途：把「定时任务调度」与「队列 Worker」注册为开机自启、崩溃自愈的常驻任务。
# 用法（管理员 PowerShell）：
#   .\deploy\windows\install-tasks.ps1 -ProjectRoot "D:\laravel12-erp" -PhpPath "D:\php\php.exe"
#
# 说明：
#   1) ERP-Scheduler：每分钟执行一次 `artisan schedule:run`（等价于 Linux crontab）
#   2) ERP-Queue    ：优先用 NSSM 注册为 Windows 服务；未安装 NSSM 时回退为「开机启动」计划任务
#   3) 卸载：Unregister-ScheduledTask -TaskName "ERP-Scheduler" -Confirm:$false

param(
    [string]$ProjectRoot = (Split-Path -Parent (Split-Path -Parent $PSScriptRoot)),
    [string]$PhpPath = "php.exe",
    [switch]$SkipQueue
)

$ErrorActionPreference = "Stop"

function Write-Step($message) {
    Write-Host "[ERP] $message" -ForegroundColor Cyan
}

if (-not (Test-Path $ProjectRoot)) {
    throw "项目目录不存在：$ProjectRoot"
}

$artisan = Join-Path $ProjectRoot "artisan"
if (-not (Test-Path $artisan)) {
    throw "未找到 artisan：$artisan"
}

# 解析 PHP 可执行文件绝对路径（schtasks 不接受依赖 PATH 的命令）
$phpCommand = Get-Command $PhpPath -ErrorAction SilentlyContinue
if (-not $phpCommand) {
    throw "未找到 PHP，请用 -PhpPath 指定 php.exe 的完整路径"
}
$phpExe = $phpCommand.Source

Write-Step "项目目录：$ProjectRoot"
Write-Step "PHP 路径  ：$phpExe"

# ---------------------------------------------------------------------------
# 1) 调度任务：每分钟执行 schedule:run
# ---------------------------------------------------------------------------
Write-Step "注册计划任务 ERP-Scheduler（每分钟）"

$action = New-ScheduledTaskAction -Execute $phpExe `
    -Argument "artisan schedule:run" `
    -WorkingDirectory $ProjectRoot

# 从当前时间起每分钟重复，持续 10 年（等价于 cron 常驻）
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) `
    -RepetitionInterval (New-TimeSpan -Minutes 1) `
    -RepetitionDuration (New-TimeSpan -Days 3650)

$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -RestartCount 3 `
    -RestartInterval (New-TimeSpan -Minutes 1)

Register-ScheduledTask -TaskName "ERP-Scheduler" `
    -Action $action -Trigger $trigger -Settings $settings `
    -Description "Laravel ERP 定时任务调度（每分钟 schedule:run）" `
    -Force | Out-Null

# ---------------------------------------------------------------------------
# 2) 队列 Worker
# ---------------------------------------------------------------------------
if ($SkipQueue) {
    Write-Step "已跳过队列 Worker 注册"
} else {
    $nssm = Get-Command "nssm.exe" -ErrorAction SilentlyContinue

    if ($nssm) {
        Write-Step "使用 NSSM 注册队列服务 ERP-Queue"
        & $nssm.Source install "ERP-Queue" $phpExe "artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --max-jobs=1000 --memory=256" | Out-Null
        & $nssm.Source set "ERP-Queue" AppDirectory $ProjectRoot | Out-Null
        & $nssm.Source set "ERP-Queue" AppStdout (Join-Path $ProjectRoot "storage\logs\worker.log") | Out-Null
        & $nssm.Source set "ERP-Queue" AppStderr (Join-Path $ProjectRoot "storage\logs\worker.log") | Out-Null
        & $nssm.Source set "ERP-Queue" AppExit Default Restart | Out-Null
        Start-Service "ERP-Queue"
        Write-Step "服务 ERP-Queue 已启动（开机自启）"
    } else {
        Write-Host "[ERP] 未检测到 NSSM，回退为「开机启动」计划任务（重启机器后才会拉起）" -ForegroundColor Yellow
        Write-Host "      生产环境建议安装 NSSM： choco install nssm  或 https://nssm.cc/download" -ForegroundColor Yellow

        $queueAction = New-ScheduledTaskAction -Execute $phpExe `
            -Argument "artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --max-jobs=1000 --memory=256" `
            -WorkingDirectory $ProjectRoot

        Register-ScheduledTask -TaskName "ERP-Queue" `
            -Action $queueAction `
            -Trigger (New-ScheduledTaskTrigger -AtStartup) `
            -Settings (New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable) `
            -Description "Laravel ERP 队列 Worker" `
            -Force | Out-Null
    }
}

Write-Step "完成。可用以下命令查看状态："
Write-Host "       Get-ScheduledTask -TaskName 'ERP-*' | Get-ScheduledTaskInfo"
Write-Host "       php artisan schedule:list"
