# 部署与运维配置

本目录存放与运行环境相关的配置模板，安装脚本会自动替换其中的项目路径。

```
deploy/
├── supervisor/
│   ├── erp-worker.conf        队列 Worker（2 进程，崩溃自愈）
│   └── erp-scheduler.conf     schedule:work 常驻（cron 的替代方案）
├── systemd/
│   ├── erp-queue.service      队列 Worker（systemd 方案）
│   └── erp-scheduler.service  调度常驻（systemd 方案）
├── cron/
│   └── laravel-erp            cron 方案：每分钟 schedule:run
└── windows/
    └── install-tasks.ps1      计划任务 ERP-Scheduler + NSSM 服务 ERP-Queue
```

## 选择建议

| 环境 | 调度 | 队列 |
|------|------|------|
| Linux 生产 | `supervisor/erp-scheduler.conf` | `supervisor/erp-worker.conf` |
| Linux 精简 | `cron/laravel-erp` | `systemd/erp-queue.service` |
| Windows 生产 | `windows/install-tasks.ps1`（计划任务） | NSSM 服务（脚本自动注册） |

调度只选一种（supervisor 或 cron），队列必须有常驻进程。

## 路径替换

模板中默认路径为 `/var/www/laravel12-erp`，使用前请替换：

```bash
sudo sed -i 's#/var/www/laravel12-erp#/srv/laravel12-erp#g' deploy/supervisor/*.conf deploy/systemd/*.service deploy/cron/laravel-erp
```

Windows 脚本通过 `-ProjectRoot` 参数指定，无需手工替换。

详细说明见 [DEPLOYMENT.md](../DEPLOYMENT.md) 的「自动化运维」章节。
