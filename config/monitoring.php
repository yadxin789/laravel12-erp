<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 业务监控与告警总开关
    |--------------------------------------------------------------------------
    |
    | 关闭后 erp:health 仍然输出检查结果，但不再发送任何告警通知，
    | 便于维护窗口期间静默（可配合 php artisan down 使用）。
    |
    */

    'enabled' => env('ERP_MONITOR_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | 告警通道
    |--------------------------------------------------------------------------
    |
    | database : 站内消息（通知中心）
    | mail     : 邮件，收件人由 recipients.emails 指定
    | webhook  : 企业微信 / 钉钉 / 飞书机器人，地址由 ERP_ALERT_WEBHOOK_URL 指定
    |
    */

    'channels' => [
        'database' => env('ERP_ALERT_CHANNEL_DATABASE', true),
        'mail' => env('ERP_ALERT_CHANNEL_MAIL', false),
        'webhook' => env('ERP_ALERT_CHANNEL_WEBHOOK', false),
    ],

    'recipients' => [
        // 站内消息按角色广播
        'roles' => explode(',', (string) env('ERP_ALERT_ROLES', 'admin')),
        // 邮件收件人，多个用逗号分隔；留空则不发
        'emails' => array_values(array_filter(array_map('trim', explode(',', (string) env('ERP_ALERT_MAIL_TO', ''))))),
    ],

    'webhook' => [
        'url' => env('ERP_ALERT_WEBHOOK_URL'),
        // wecom（企业微信）/ dingtalk（钉钉）/ feishu（飞书）
        'driver' => env('ERP_ALERT_WEBHOOK_DRIVER', 'wecom'),
        // 钉钉需要加签时填写
        'secret' => env('ERP_ALERT_WEBHOOK_SECRET'),
        'timeout' => (int) env('ERP_ALERT_WEBHOOK_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | 告警抑制（冷却）
    |--------------------------------------------------------------------------
    |
    | 同一 fingerprint 在上次通知后 cooldown_minutes 分钟内不再重复发送，
    | 避免故障期间刷屏；但 occurrences 计数会持续累加。
    | 升级：firing 累计超过 escalation_after 次后，忽略冷却再发一次。
    |
    */

    'cooldown_minutes' => (int) env('ERP_ALERT_COOLDOWN', 30),
    'escalation_after' => (int) env('ERP_ALERT_ESCALATION_AFTER', 10),

    /*
    |--------------------------------------------------------------------------
    | 健康检查阈值
    |--------------------------------------------------------------------------
    */

    'thresholds' => [
        // 待处理队列积压
        'queue_pending_warning' => (int) env('ERP_QUEUE_PENDING_WARNING', 100),
        'queue_pending_critical' => (int) env('ERP_QUEUE_PENDING_CRITICAL', 1000),
        // 近 1 小时失败任务数
        'failed_jobs_warning' => (int) env('ERP_FAILED_JOBS_WARNING', 1),
        'failed_jobs_critical' => (int) env('ERP_FAILED_JOBS_CRITICAL', 20),
        // 队列最长等待时间（分钟）：超过说明 worker 可能已停
        'queue_lag_minutes' => (int) env('ERP_QUEUE_LAG_MINUTES', 15),
        // 磁盘可用空间阈值（百分比）
        'disk_free_warning' => (int) env('ERP_DISK_FREE_WARNING', 20),
        'disk_free_critical' => (int) env('ERP_DISK_FREE_CRITICAL', 10),
        // 备份最大允许“陈旧”时长（小时）
        'backup_max_age_hours' => (int) env('ERP_BACKUP_MAX_AGE_HOURS', 26),
        // 数据库连接数占比
        'db_connections_warning' => (int) env('ERP_DB_CONN_WARNING', 80),
    ],

    /*
    |--------------------------------------------------------------------------
    | 低库存预警
    |--------------------------------------------------------------------------
    |
    | 检查频率由 routes/console.php 中的调度决定。
    |
    */

    'low_stock' => [
        'enabled' => env('ERP_LOW_STOCK_ENABLED', true),
        'role' => env('ERP_LOW_STOCK_ROLE', 'warehouse_manager'),
    ],

    /*
    |--------------------------------------------------------------------------
    | 状态自动转换扫描（erp:state-transition，每 5 秒一次）
    |--------------------------------------------------------------------------
    |
    | 把「数量达标 / 到期 / 物料重新齐套」驱动的状态跃迁补算一遍，规则见 StateTransitionService。
    | modules 留空表示全部启用，可用值：quotation / production_order / sales_order /
    | purchase_order / inventory_reservation；scan_limit 限制单次每模块处理量，避免长事务。
    |
    */

    'state_transition' => [
        'enabled' => env('ERP_STATE_TRANSITION_ENABLED', true),
        'modules' => array_values(array_filter(array_map('trim', explode(',', (string) env('ERP_STATE_TRANSITION_MODULES', ''))))),
        'scan_limit' => (int) env('ERP_STATE_TRANSITION_LIMIT', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | 数据库备份
    |--------------------------------------------------------------------------
    */

    'backup' => [
        'enabled' => env('ERP_BACKUP_ENABLED', true),
        'disk' => env('ERP_BACKUP_DISK', 'local'),
        'directory' => env('ERP_BACKUP_DIR', 'backups'),
        // 保留天数，超出自动清理
        'retention_days' => (int) env('ERP_BACKUP_RETENTION_DAYS', 14),
        'compress' => env('ERP_BACKUP_COMPRESS', true),
        // mysqldump / pg_dump 可执行文件路径，留空则自动探测
        'mysqldump_path' => env('ERP_MYSQLDUMP_PATH'),
        'pg_dump_path' => env('ERP_PGDUMP_PATH'),
        'timeout' => (int) env('ERP_BACKUP_TIMEOUT', 1800),
    ],

    /*
    |--------------------------------------------------------------------------
    | 过期数据清理（天）
    |--------------------------------------------------------------------------
    */

    'prune' => [
        'sessions' => (int) env('ERP_PRUNE_SESSIONS_DAYS', 7),
        'notifications' => (int) env('ERP_PRUNE_NOTIFICATIONS_DAYS', 90),
        'failed_jobs' => (int) env('ERP_PRUNE_FAILED_JOBS_DAYS', 30),
        'job_batches' => (int) env('ERP_PRUNE_BATCHES_DAYS', 30),
        'audit_logs' => (int) env('ERP_PRUNE_AUDIT_LOGS_DAYS', 180),
        'password_resets' => (int) env('ERP_PRUNE_PASSWORD_RESETS_DAYS', 1),
    ],
];
