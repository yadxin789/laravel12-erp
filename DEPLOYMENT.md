# 电子工厂ERP系统部署文档

## 系统概述

基于 Laravel 12 + MySQL 8 的电子工厂 ERP 系统，覆盖销售、采购、库存、生产、质量、财务、CRM、AI、协作沟通、物流快递等 20 个业务模块。内置 8 角色 RBAC 权限、Spatie/Permission（11 个 Policy）、Sanctum API，前端为 Blade + Bootstrap 5 本地静态资源（无需 npm 构建）。

## 环境要求

| 组件 | 版本 |
|------|------|
| PHP | 8.4+ |
| MySQL | 8.x（推荐）/ SQLite 3（开发备选） |
| Node.js | 18+ |
| Composer | 最新稳定版 |

## 安装步骤

### 1. 克隆项目

```bash
git clone <repository-url>
cd laravel12-erp
```

### 2. 安装依赖

```bash
composer install
# npm install 可跳过：前端样式为 public/css、public/js 下的本地静态资源
```

### 3. 配置环境

```bash
cp .env.example .env
php artisan key:generate
```

编辑 `.env` 文件：

```env
APP_NAME=电子工厂ERP系统
APP_ENV=production
APP_KEY=                       # 由 key:generate 生成
APP_DEBUG=false
APP_URL=http://your-domain.com

# 数据库（MySQL 推荐）
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel12_erp
DB_USERNAME=root
DB_PASSWORD=root

# 缓存/会话（生产建议 redis）
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Sanctum
SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

### 4. 创建数据库

```bash
mysql -u root -p -e "CREATE DATABASE laravel12_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. 数据库迁移

```bash
php artisan migrate
```

### 6. 数据库填充（可选 - 演示数据）

```bash
php artisan db:seed
# 或单独运行：
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=DemoDataSeeder
php artisan db:seed --class=ExtendedDemoDataSeeder
```

填充内容（执行 `db:seed` 默认按顺序执行 3 个）：
- `RolePermissionSeeder`：8 角色 + 90+ 权限
- `DemoDataSeeder`：用户/物料/客户/供应商/仓库/BOM/路由/工单等基础业务数据
- `ExtendedDemoDataSeeder`：发货/退货/收料/生产计划/领料/汇报/完工入库/财务/CRM/审批流/HR/项目/资产/电商/通知/AI 配置

### 7. 创建存储链接

```bash
php artisan storage:link
```

> 必须执行：用户头像（`avatars/`）、群聊/私聊附件（`chat-files/`）、物料图片与单据附件都走 `public` 盘。
> AI 聊天附件走私有盘 `ai_chat`（`storage/app/ai-chat`），不对外暴露，经控制器预览/下载。

### 8. 前端资源（无需构建）

样式与脚本已内置在 `public/`：

| 资源 | 路径 |
|------|------|
| Bootstrap 5 CSS / JS | `public/css/bootstrap.min.css`、`public/js/bootstrap.bundle.min.js` |
| FontAwesome | `public/css/font-awesome.min.css` |
| ECharts | `public/js/echarts.min.js` |
| 自定义样式 | `public/css/erp.css`、`public/css/erp-utilities.css` |
| 默认头像 | `public/avatars/defaults/avatar1~5.svg` |

如需自定义主题，直接改 `public/css/erp.css` 后清浏览器缓存即可。

### 9. 启动服务

```bash
php artisan serve            # 开发环境
# 或通过 PHP-FPM + Nginx（生产环境，见下文 Nginx 配置）
php artisan queue:work      # 队列监听（另开终端）
```

## 验证

```bash
# 单元 + Feature 测试
php artisan test

# 全角色全页面冒烟测试
php artisan test --filter=SmokeTest

# 查看冒烟测试报告
cat storage/logs/smoke-report.txt
```

冒烟测试会逐角色（admin / sales_manager / purchase_manager / production_manager / warehouse_manager / quality_manager / accountant / viewer）爬取 145+ 个页面，验证全部返回 2xx/3xx。

2026-10-22 另做了一轮**真实 HTTP 内核冒烟**（路由 → 中间件 → 控制器 → Blade，会话注入真实用户）：

- 166 条 GET 路由：OK=166 / FAIL=0 / 404=0
- 对每页 HTML 做内容校验：Tailwind 残留 0、错误页/`Undefined variable`/`SQLSTATE` 0、未渲染的 `{{ }}`/`:class=` 0、缺样式资源 0（两个条码打印页自带内联样式，属预期）
- 8 角色 × 9 页面的权限矩阵全部符合预期

## Web 服务器配置

### Nginx 配置示例

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/laravel12-erp/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-Referrer-Policy "strict-origin-when-cross-origin";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Storage 静态资源缓存
    location ~* /storage/(.+)\.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 30d;
        access_log off;
    }
}
```

### Apache 配置示例

确保启用 `mod_rewrite`，并在 `public/.htaccess` 文件中配置：

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    RewriteRule ^(.*)/$ /$1 [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## 目录结构

```
laravel12-erp/
├── app/
│   ├── Enums/                # 11 个业务状态枚举
│   ├── Events/               # 业务事件
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── App/          # 业务控制器
│   │   │   ├── Admin/        # 系统管理控制器
│   │   │   ├── MasterData/   # 主数据控制器
│   │   │   ├── Finance/      # 财务 API
│   │   │   ├── Sales/        # 销售 API (JSON)
│   │   │   ├── Purchasing/   # 采购 API (JSON)
│   │   │   ├── Production/   # 生产 API (JSON)
│   │   │   └── Inventory/    # 库存 API (JSON)
│   │   ├── Middleware/
│   │   └── Requests/         # 表单请求验证
│   ├── Listeners/            # 事件订阅器
│   ├── Mail/                 # 邮件模板
│   ├── Models/               # 87 个模型（含 System/AI、Chat、Logistics）
│   ├── Policies/             # 11 个授权策略
│   ├── Providers/            # 服务提供者（AuthServiceProvider 必须在 bootstrap/providers.php 注册）
│   ├── Services/             # 11 个业务服务层（含 AiService、LogisticsService、NotificationService）
│   └── Support/              # 工具类
├── bootstrap/
├── config/                   # 配置文件
├── database/
│   ├── factories/            # 模型工厂
│   ├── migrations/           # 81 个迁移
│   └── seeders/              # 3 个种子
├── public/                   # Web 入口（css/ js/ avatars/ 前端静态资源）
├── resources/
│   ├── views/                # Blade 模板（172 个）
│   ├── css/                  # 备用样式入口
│   └── js/                   # 备用脚本入口
├── routes/
│   ├── web.php               # Web 路由（321 条）
│   ├── api.php               # API 路由
│   ├── auth.php              # 认证路由
│   └── console.php
├── storage/                  # 运行时存储
│   ├── app/
│   ├── framework/
│   └── logs/                 # 包含 smoke-report.txt
└── tests/
    └── Feature/              # Feature/Controller 测试
```

## 核心模块

### 主数据管理
- 物料管理 (Items) — 含 barcode、图片、SKU
- 客户管理 (Customers)
- 供应商管理 (Suppliers)
- 仓库/库位 (Warehouses/Locations)
- BOM 头/行 (BomHeader/BomLine)
- 工艺路线头/行 (RoutingHeader/RoutingOperation)
- 工作中心/生产线

### 销售管理
- 报价单 → 销售订单 → 发货单 → 销售退货
- 提交/审核/取消/关闭状态机
- 信用额度自动校验
- 行项目金额、税额自动计算

### 采购管理
- 请购单 → 采购订单 → 收货单 → 采购退货
- 提交/审核/取消/状态机
- 三方校验（数量/金额/批次）

### 库存管理
- 库存查询（多维度：仓库/库位/批次/质量状态）
- 库存调拨、调整、冻结/解冻
- 盘点管理（盘点单 + 差异行）
- 批次/序列号追踪
- 低库存预警

### 生产管理
- 生产计划（MRP 运算） → 生产工单
- 工单下达/开工/汇报/完工/关闭/取消
- 领料单（按工单配物料）
- BOM 替换、工艺路线版本控制
- 计划成本 vs 实际成本

### 质量管理
- IQC 来料检验（按收货单）
- IPQC 过程检验（按工单）
- FQC 成品检验（按完工入库）
- OQC 出货检验（按发货单）
- 缺陷代码、缺陷记录、改判
- 质量统计报表

### 财务管理
- 客户发票（应收），关联销售订单
- 供应商账单（应付），关联采购订单
- 收付款（分收/付），关联发票/账单
- 自动核销、未收/未付余额
- 对账单（按客户/供应商）

### CRM
- 销售线索（含来源、状态、负责人）
- 商机（含阶段、概率、预计金额、预计成交日）
- 跟进记录（通话/邮件/会议/备注）
- 销售漏斗看板（按阶段聚合）

### 审批流
- 审批流配置（含启用开关）
- 审批节点（角色或用户）
- 审批任务（多级串行）
- 我的待办、待审批列表

### 报表中心
- 销售统计（按客户/物料/期间）
- 采购统计（按供应商/物料/期间）
- 库存周转（按物料）
- 质量统计（合格率/缺陷分布）

### AI 能力
- AI 供应商管理（DeepSeek / 通义千问 / 豆包 / 元宝 **可同时配置**：独立 Key、独立开关、设默认、单家连通性测试）
- 模型目录管理（按供应商同步 / 自定义 / 设默认 / 是否开放用户 / 看图能力）
- AI 聊天（多会话、模型切换、附件与图片理解、Markdown 渲染、导出 Markdown）
- 发票 OCR（图片→结构化字段）
- AI 审批建议（订单详情页）
- AI 智能分析中心（10 个分析模块）

### 协作沟通
- 全员群聊（`/app/chat`）：图片/文件附件、撤回自己的消息、5 秒轮询新消息、成员列表显示在线状态
- 一对一私聊（`/app/chat/{user}`）：联系人按最后消息排序，对方离线时自动落 `private_message` 站内通知
- 「清空我的记录」基于 `chat_clear_states`，只影响本人视图，不影响其他参与者

### 个人中心
- 头像上传（jpg/png/gif/webp ≤ 2MB，自动清理旧文件）
- 5 套系统默认 SVG 头像一键选择
- 个人资料（姓名/邮箱/电话/岗位/签名）+ 名片卡（角色、在线状态、最近活跃）

### 物流快递
- 后台开通引导页 `/admin/logistics/guide`：快递100 / 快递鸟 / 菜鸟 通道对比 + 官方申请链接 + 三步走
- 物流设置 `/admin/logistics/settings`：查询通道（AppKey/AppSecret/默认/测试/开关）、10 家国内快递公司启用清单、高级参数
- 仓库物流查询 `/app/inventory/logistics`：运单查询、轨迹时间线、运单登记、运单详情
- 查询三档回退：正式签名 API → 快递100 免费公共查询 → 演示数据（页面明确标注）
- 物流状态变化站内通知；签收/异常/退回额外通知 `warehouse_manager` 角色

## 角色权限

| 角色 | 说明 |
|------|------|
| admin | 管理员，拥有所有权限 |
| sales_manager | 销售经理 |
| purchase_manager | 采购经理 |
| production_manager | 生产经理 |
| warehouse_manager | 仓库经理 |
| quality_manager | 质量经理 |
| accountant | 财务人员（只读） |
| viewer | 查看员（只读） |
| demo | 虚拟演示角色（全业务模块 + 全部业务权限，用于演示所有操作页面；进不了后台） |

## 默认账号

| 角色 | 账号 | 密码 |
|------|------|------|
| admin | admin@admin.com | 123456 |
| 销售经理 | sales@demo.com | 123456 |
| 采购经理 | purchase@demo.com | 123456 |
| 生产经理 | production@demo.com | 123456 |
| 仓库经理 | warehouse@demo.com | 123456 |
| 质量经理 | quality@demo.com | 123456 |
| 财务 | accounting@demo.com | 123456 |
| 查看员 | viewer@demo.com | 123456 |

> ⚠️ 生产环境必须立即修改默认密码！

## 测试

```bash
# 全部测试
php artisan test

# 冒烟测试（155+ 页面 × 8 角色 ≈ 1240 个请求）
php artisan test --filter=SmokeTest
```

## 故障排查

| 现象 | 排查 |
|------|------|
| 500 错误 | 检查 `storage/logs/laravel.log` |
| 迁移失败 | `php artisan migrate:status` 查看 Pending 迁移 |
| 前端样式异常 | 确认 `public/css/bootstrap.min.css` 与 `public/css/erp.css` 可访问；无需 `npm run build` |
| 头像/聊天附件 404 | `php artisan storage:link`，确认 `public/storage` 符号链接存在且 `storage/app/public` 可写 |
| AI 调用一直失败 | 后台 `/admin/ai-settings` 检查供应商是否启用、Key 是否填写、Base URL 是否正确；用单家「测试」按钮定位 |
| 物流查询返回演示数据 | 说明未配置通道密钥或外网不可达；配置 Key 后走正式 API，否则走免费公共查询 |
| 冒烟测试失败 | 查看 `storage/logs/smoke-report.txt` 定位失败路由 |
| 队列任务卡住 | `php artisan queue:flush` + `queue:work` |
| 所有接口 403（含 admin） | 检查 `bootstrap/providers.php` 是否注册了 `AuthServiceProvider`（未注册则 11 个 Policy 全部失效） |

## 安全建议

1. 生产环境 `APP_DEBUG=false`
2. `.env` 文件权限 600，禁用 `phpinfo()`
3. 启用 HTTPS 与 HSTS
4. 定期 `composer update` 升级依赖
5. CORS 白名单 + Sanctum 域白名单
6. 定期备份数据库与 `storage/app/`

## 自动化运维

这一章补齐了「定时任务 / 队列守护 / 状态监控 / 备份 / 告警 / 自启动」六项运维能力，
目标是让系统在没有人工干预的情况下也能自愈、自报、可回溯。

### 1. 能力总览

| 能力 | 实现 | 入口 |
|------|------|------|
| 任务调度 | Laravel Scheduler（`routes/console.php`） | `php artisan schedule:list` |
| 队列消费 | `queue:work` + supervisor/systemd/NSSM | `deploy/supervisor/`、`deploy/systemd/`、`deploy/windows/` |
| 状态监控 | `erp:health`（系统面 + 业务面） | `php artisan erp:health` |
| 监控面板 | 后台 → 系统监控 | `/admin/system-health` |
| 数据库备份 | `db:backup`（mysqldump/pg_dump/sqlite + gzip） | `php artisan db:backup` |
| 备份恢复 | `db:restore` | `php artisan db:restore <file>` |
| 告警通知 | `AlertService`（站内 / 邮件 / 群机器人，带冷却与恢复） | `config/monitoring.php` |
| 数据轮转 | `erp:prune` | `php artisan erp:prune` |
| 状态自动转换 | `erp:state-transition`（5 秒一次） | `php artisan erp:state-transition --dry-run` |
| 一键安装 | `scripts/install.sh`、`scripts/install.ps1` | — |
| 一键发布 | `scripts/deploy.sh`、`scripts/deploy.ps1` | — |
| 外部拨测 | `scripts/health-check.sh`（退出码 0/1/2/3） | — |

### 2. 一键安装

```bash
# Linux / macOS
./scripts/install.sh                 # 完整安装（依赖、迁移、种子、守护进程）
./scripts/install.sh --no-seed       # 生产环境：不导入演示数据
./scripts/install.sh --no-daemon     # 只装应用，不配置守护进程
```

```powershell
# Windows（管理员 PowerShell）
.\scripts\install.ps1
.\scripts\install.ps1 -NoSeed
```

安装脚本会自动完成：环境校验 → 依赖安装 → `.env` 生成 → 目录权限 → 迁移 → `storage:link`
→ 生产缓存 → 守护进程注册 → 首次健康检查。

### 3. 自启动与进程守护

**Linux（推荐 supervisor）**

```bash
sudo cp deploy/supervisor/*.conf /etc/supervisor/conf.d/
sudo sed -i 's#/var/www/laravel12-erp#/实际项目路径#g' /etc/supervisor/conf.d/erp-*.conf
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl status
```

- `erp-worker`：`numprocs=2` 双进程消费，`autorestart=true` 崩溃自愈
- `erp-scheduler`：`schedule:work` 常驻，等价于每分钟执行 `schedule:run`

**Linux（systemd 替代方案）**

```bash
sudo cp deploy/systemd/*.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now erp-queue erp-scheduler
```

**Linux（仅 cron 方案）**

```bash
sudo cp deploy/cron/laravel-erp /etc/cron.d/laravel-erp
sudo chmod 644 /etc/cron.d/laravel-erp
```

> cron 只解决调度，队列 Worker 仍需 supervisor/systemd 常驻。
>
> `erp:state-transition` 是亚分钟任务（`everyFiveSeconds`）：cron / 计划任务仍然只需每分钟拉起
> 一次 `schedule:run`，Laravel 会在该分钟的剩余时间内持续轮询到期的任务，因此**不需要**把
> 系统级 cron 改成每 5 秒。前提是不能使用 `array` 缓存驱动（`withoutOverlapping()` 依赖缓存锁）。

**Windows**

```powershell
# 注册计划任务：ERP-Scheduler（每分钟 schedule:run）+ ERP-Queue（NSSM 服务，无 NSSM 时回退开机启动）
.\deploy\windows\install-tasks.ps1 -ProjectRoot "D:\laravel12-erp" -PhpPath "D:\php\php.exe"
```

生产环境建议安装 NSSM（`choco install nssm`），把队列封装为真正的 Windows 服务，
获得崩溃自愈能力；计划任务方案只能保证开机拉起。

**Worker 参数为什么这样写**

```
queue:work database --sleep=3 --tries=3 --max-time=3600 --max-jobs=1000 --memory=256
```

`--max-time` / `--max-jobs` 让进程定期自行退出并由 supervisor 拉起，既释放长驻内存，
也保证发布新代码后 Worker 不会一直跑旧逻辑（配合 `php artisan queue:restart`）。

### 4. 定时任务清单

| 频率 | 命令 | 作用 |
|------|------|------|
| 每分钟 | `erp:health` | 健康检查 + 调度心跳 + 告警 |
| 每天 02:00 | `db:backup` | 数据库全量备份 |
| 每天 03:10 | `erp:prune` | 会话/通知/失败任务/审计日志轮转 |
| 每天 08:00 | `erp:low-stock-alert` | 低库存汇总预警 |
| 每小时 | `erp:logistics-track` | 在途运单轨迹轮询，状态变化自动通知 |
| 每 30 分钟 | `erp:ecommerce-sync` | 电商订单同步（Job 自带平台级互斥） |
| 每天 | `queue:prune-batches` / `queue:prune-failed` / `auth:clear-resets` | 框架收尾清理 |

调度输出统一落盘便于排错：

- `storage/logs/health.log`
- `storage/logs/scheduler.log`
- `storage/logs/worker.log`

### 5. 状态监控

```bash
php artisan erp:health              # 人读表格
php artisan erp:health --json       # 机读，供 Zabbix / Prometheus textfile 采集
php artisan erp:health --no-alert   # 只检查不告警（维护窗口）
./scripts/health-check.sh https://erp.example.com
```

检查项分两类：

- **系统面**：数据库连通性/连接数、队列积压、队列最长等待、失败任务、磁盘可用空间、备份新鲜度、调度心跳
- **业务面**：负库存、待审批积压、逾期未收回应收、延期生产工单

后台 **系统监控**（`/admin/system-health`）展示同一份结果，并可直接「确认恢复」告警。

`health-check.sh` 退出码：`0` 健康 / `1` 有警告 / `2` 有异常 / `3` 站点不可达。

### 6. 告警与通知

配置文件 `config/monitoring.php`，全部可用环境变量覆盖。

告警语义对齐 Prometheus Alertmanager：

- `fire()` 触发并累加，同一 `fingerprint` 在冷却期内不重复发送（`ERP_ALERT_COOLDOWN`，默认 30 分钟）
- 持续触发达到 `ERP_ALERT_ESCALATION_AFTER`（默认 10 次）会强制升级再发一次
- 指标恢复正常后自动发送「已恢复」通知（含持续时长）

三条通道：

```env
ERP_ALERT_CHANNEL_DATABASE=true    # 站内消息（通知中心），按角色广播
ERP_ALERT_CHANNEL_MAIL=true        # 邮件
ERP_ALERT_MAIL_TO=ops@example.com
ERP_ALERT_CHANNEL_WEBHOOK=true     # 群机器人
ERP_ALERT_WEBHOOK_URL=https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=xxx
ERP_ALERT_WEBHOOK_DRIVER=wecom     # wecom / dingtalk / feishu
```

除轮询式告警外，以下两类事件是**即时**告警：

- 队列任务失败（`JobFailed` 事件）
- 应用未捕获异常（5xx 类，已排除 404/422/权限/限流等业务性异常）

告警记录存 `system_alerts` 表，可在监控面板确认恢复；`ERP_MONITOR_ENABLED=false` 可全局静默。

### 7. 备份与恢复

```bash
php artisan db:backup            # 立即备份（默认库）
php artisan db:backup --list     # 查看备份台账（含大小/耗时/状态）
php artisan db:restore storage/app/backups/xxx.sql.gz
./scripts/backup.sh --sync /mnt/nas   # 备份并同步到异地
```

- 备份文件：`storage/app/backups/`，gzip 压缩，默认保留 14 天（`ERP_BACKUP_RETENTION_DAYS`）
- 每次备份写入 `database_backups` 台账，**备份是否成功本身被监控**：超过 26 小时没有成功备份即告警
- 找不到 `mysqldump` 时用 `ERP_MYSQLDUMP_PATH` 指定完整路径

> 备份的价值取决于能否恢复。建议每季度做一次恢复演练：
> `php artisan down` → `db:restore` → 抽查数据 → `php artisan up`。

### 8. 发布流程

```bash
./scripts/deploy.sh main       # Linux
.\scripts\deploy.ps1 -Branch main   # Windows
```

流程：维护模式 → 拉代码 → 依赖安装 → **迁移前备份** → 迁移 → 重建缓存 → `queue:restart` → 退出维护模式 → 健康检查。

> 发布后不执行 `queue:restart`，Worker 会继续跑旧代码，这是最常见的上线事故。

### 9. 新增排查项

| 现象 | 排查 |
|------|------|
| 定时任务没执行 | `php artisan schedule:list` 有清单不代表在跑；看监控面板「调度心跳」是否超过 10 分钟 |
| 心跳正常但任务没跑 | 看 `storage/logs/scheduler.log`；`withoutOverlapping` 依赖缓存锁，确认 `CACHE_STORE` 不是 `array` |
| 队列不消费 | `supervisorctl status` / `systemctl status erp-queue`；Windows 看 NSSM 服务状态 |
| 任务被重复执行 | `DB_QUEUE_RETRY_AFTER` 必须 ≥ 所有 Job 的 `$timeout` |
| 备份失败 | 多为找不到 `mysqldump`，配置 `ERP_MYSQLDUMP_PATH` |
| 告警刷屏 | 调大 `ERP_ALERT_COOLDOWN`；确认 fingerprint 是否稳定（不要用随机值） |
| 队列任务卡住 | `php artisan queue:flush` + 重启 Worker（supervisor 会自动拉起） |

## 维护

```bash
# 清理缓存
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 重新加载配置（生产）
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
