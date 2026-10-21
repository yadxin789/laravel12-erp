# 电子工厂ERP系统部署文档

## 系统概述

基于 Laravel 12 + MySQL 8 的电子工厂 ERP 系统，覆盖销售、采购、库存、生产、质量、财务、CRM、AI 等 16 个业务模块。内置 8 角色 RBAC 权限、Spatie/Permission、Sanctum API、Vite 前端构建。

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
npm install
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

### 8. 前端构建

```bash
npm run build
```

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
│   ├── Models/               # 75+ 个模型
│   ├── Policies/             # 11 个授权策略
│   ├── Providers/            # 服务提供者
│   ├── Services/             # 9 个业务服务层
│   └── Support/              # 工具类
├── bootstrap/
├── config/                   # 配置文件
├── database/
│   ├── factories/            # 模型工厂
│   ├── migrations/           # 75+ 个迁移
│   └── seeders/              # 3 个种子
├── public/                   # Web 入口
├── resources/
│   ├── views/                # Blade 模板
│   ├── css/                  # Tailwind 入口
│   └── js/                   # JS 入口
├── routes/
│   ├── web.php               # Web 路由
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
- AI 设置（4 家国内厂商 + 1 家国际）
- 发票 OCR（图片→结构化字段）
- AI 聊天助手（自然语言查库存/订单）
- AI 审批建议（订单详情页）
- AI 智能分析中心（10 个分析模块）

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

## 默认账号

| 角色 | 账号 | 密码 |
|------|------|------|
| admin | admin@admin.com | password |
| 销售经理 | sales@demo.com | password |
| 采购经理 | purchase@demo.com | password |
| 生产经理 | production@demo.com | password |
| 仓库经理 | warehouse@demo.com | password |
| 质量经理 | quality@demo.com | password |
| 财务 | accounting@demo.com | password |
| 查看员 | viewer@demo.com | password |

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
| 前端资源 404 | `php artisan storage:link && npm run build` |
| 冒烟测试失败 | 查看 `storage/logs/smoke-report.txt` 定位失败路由 |
| 队列任务卡住 | `php artisan queue:flush` + `queue:work` |

## 安全建议

1. 生产环境 `APP_DEBUG=false`
2. `.env` 文件权限 600，禁用 `phpinfo()`
3. 启用 HTTPS 与 HSTS
4. 定期 `composer update` 升级依赖
5. CORS 白名单 + Sanctum 域白名单
6. 定期备份数据库与 `storage/app/`

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
