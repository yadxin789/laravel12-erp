# 电子工厂 ERP 系统

基于 Laravel 12 的电子制造企业资源计划（ERP）系统，覆盖销售、采购、生产、库存、质量、财务、CRM、AI 智能化等全流程。

## 技术栈

- **后端**: Laravel 12 + PHP 8.4
- **数据库**: MySQL 8.4（推荐）/ SQLite 3（开发备选）
- **前端**: Blade + Tailwind CSS（Vite 构建）
- **权限**: Spatie/Laravel-Permission (RBAC)
- **API**: Laravel Sanctum
- **队列**: Database Driver

## 功能模块

| 模块 | 功能 |
|------|------|
| **主数据** | 物料、客户、供应商、仓库/库位、BOM、工艺路线、生产线、工作中心 |
| **销售管理** | 报价单 → 销售订单 → 发货单 → 销售退货，信用额度自动校验 |
| **采购管理** | 请购单 → 采购订单 → 收货单 → 采购退货 |
| **生产管理** | 生产计划（MRP）→ 生产工单 → 领料/汇报/完工入库 |
| **库存管理** | 库存查询、调拨、调整、冻结/解冻、盘点、批次追溯、低库存预警、扫码出入库 |
| **质量管理** | IQC/IPQC/FQC/OQC 检验、缺陷代码、缺陷记录、质量报表 |
| **财务管理** | 客户发票（应收）、供应商账单（应付）、收付款核销、对账单 |
| **CRM** | 销售线索、商机管理、跟进记录、销售漏斗看板 |
| **审批流** | 可配置多级审批（角色/用户）、审批节点、我的待办、待审批列表 |
| **报表中心** | 销售/采购/库存/质量四大统计报表 |
| **HR** | 员工档案、考勤记录 |
| **项目管理** | 项目、任务、工时统计 |
| **资产管理** | 固定资产登记、月折旧、累计折旧、净值自动计算 |
| **电商对接** | 淘宝/京东/亚马逊/拼多多订单同步 |
| **AI 能力** | 多厂商 AI 设置（DeepSeek/通义/豆包/元宝）、发票 OCR 识别、自然语言查库存、AI 审批建议、AI 开通引导页 |
| **AI 智能分析中心** | 经营日报、库存预警分析（AI 补货建议）、客户信用评估、供应商质量评分、销售预测、生产成本差异分析、异常订单检测、智能排程建议、电商订单合并、邮件智能回复 |
| **系统管理** | 用户、角色、权限、审计日志、通知中心、编号序列、附件管理 |

## 快速开始

### 环境要求

- PHP >= 8.4
- Composer
- Node.js >= 18
- MySQL 8.x（或 SQLite 3）

### 安装

```bash
# 克隆项目
git clone <repository-url>
cd laravel12-erp

# 安装依赖
composer install
npm install

# 配置环境
cp .env.example .env
php artisan key:generate

# 创建数据库（MySQL）
mysql -u root -p -e "CREATE DATABASE laravel12_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 修改 .env 中 DB_* 段，填写数据库连接信息

# 运行迁移和填充
php artisan migrate --seed

# 创建存储链接
php artisan storage:link

# 前端构建
npm run build

# 启动开发服务器
php artisan serve
```

### 默认账号

| 角色 | 账号 | 密码 | 说明 |
|------|------|------|------|
| admin | admin@admin.com | password | 系统管理员，全部权限 |
| 销售经理 | sales@demo.com | password | 销售模块全部操作 |
| 采购经理 | purchase@demo.com | password | 采购模块全部操作 |
| 生产经理 | production@demo.com | password | 生产模块全部操作 |
| 仓库经理 | warehouse@demo.com | password | 库存模块全部操作 |
| 质量经理 | quality@demo.com | password | 质量模块全部操作 |
| 财务 | accounting@demo.com | password | 只读查看各模块 |
| 查看员 | viewer@demo.com | password | 只读查看各模块 |

> 所有 demo 角色共享密码 `password`。

## 演示数据

运行 `php artisan migrate --seed` 后会创建以下演示数据：

| 数据 | 数量 |
|------|------|
| 角色 | 8 |
| 用户 | 8 |
| 物料 | 11 |
| 客户/供应商 | 各 5+ |
| 仓库/库位 | 4 / 8 |
| 报价单 | 3 |
| 销售订单 | 7 |
| 采购订单 | 7 |
| 生产工单 | 6 |
| 库存余额 | 14 |
| 质检单 | 6 |
| 发货单 / 销售退货 | 3 / 2 |
| 收货单 / 采购退货 | 3 / 2 |
| 生产计划 / 领料 / 汇报 / 完工入库 | 2 / 2 / 3 / 2 |
| 客户发票 / 供应商账单 / 收付款 | 3 / 2 / 3 |
| CRM 线索 / 商机 / 跟进 | 5 / 4 / 6 |
| 员工 / 考勤 | 8 / 40 |
| 项目 / 任务 / 工时 | 2 / 5 / 5 |
| 资产 | 5 |
| 电商订单 | 6（4 个平台） |
| 通知 | 18 |
| AI 设置 | 1（DeepSeek 默认） |

种子分两次执行：`RolePermissionSeeder` → `DemoDataSeeder` → `ExtendedDemoDataSeeder`，全部幂等（已存在则跳过）。

## 目录结构

```
app/
├── Enums/              # 业务状态枚举（11个）
├── Events/             # 事件（订单提交/审批、发货确认、质检完成、库存变动）
├── Http/
│   ├── Controllers/
│   │   ├── App/        # 业务控制器（销售/采购/生产/库存/质量）
│   │   ├── Admin/      # 系统管理控制器
│   │   ├── MasterData/  # 主数据控制器
│   │   ├── Sales/      # 销售 API 控制器（JSON）
│   │   ├── Purchasing/ # 采购 API 控制器（JSON）
│   │   ├── Production/ # 生产 API 控制器（JSON）
│   │   └── Inventory/  # 库存 API 控制器（JSON）
│   └── Middleware/
├── Listeners/          # 事件订阅器（审计日志 + 通知）
├── Models/             # 75+ 个模型
├── Policies/           # 11 个授权策略
├── Services/           # 9 个业务服务层
└── Support/            # 工具类（NumberGenerator、Money、AuditLogger）
database/
├── migrations/         # 75+ 个迁移
└── seeders/            # 角色权限 + 基础演示 + 扩展演示
resources/
└── views/
    ├── layouts/        # admin / app / business 三套布局
    ├── components/     # 可复用 Blade 组件
    ├── app/            # 业务视图
    └── masterdata/     # 主数据视图
routes/
├── web.php             # Web 路由
├── api.php             # API 路由
└── auth.php            # 认证路由
tests/
└── Feature/            # Feature/Controller 测试（AuthTest、CrmTest、SmokeTest）
```

## 业务流程

### 销售流程
```
报价单(draft→submitted→won)
  └→ 销售订单(draft→submitted→approved→partially_delivered→fully_delivered→closed)
       └→ 发货单(draft→confirmed) ──→ 库存出库
       └→ 销售退货(draft→approved)
```

### 采购流程
```
请购单(draft→approved)
  └→ 采购订单(draft→submitted→approved→partially_received→fully_received→closed)
       └→ 收货单(draft→confirmed) ──→ 库存入库
       └→ 采购退货(draft→approved)
```

### 生产流程
```
生产计划(draft→approved→converted)
  └→ MRP 运算自动生成计划
  └→ 生产工单(draft→ready→in_progress→completed→closed)
       ├→ 领料单(draft→confirmed) ──→ 库存出库
       ├→ 生产汇报 ──→ 回写完成数量，达成自动完工
       └→ 完工入库(draft→confirmed) ──→ 库存入库
```

## AI 功能使用指南

### 快速开通 AI

首次点击顶部导航"🤖 AI 助手"，系统自动跳转到开通引导页（`/app/ai/welcome`）：

1. **选择提供商**：4 家国内 AI 厂商对比（DeepSeek / 通义千问 / 豆包 / 腾讯元宝），含价格和新用户福利
2. **获取 API Key**：点击卡片中的链接一键跳转注册（DeepSeek 推荐，新用户送 ¥10 额度）
3. **粘贴 Key**：在引导页粘贴并保存，即可使用全部 AI 功能

### AI 智能分析中心模块

| 模块 | 路由 | 功能 |
|------|------|------|
| 📰 经营日报 | `/app/ai/daily-report` | AI 自动生成每日经营摘要 |
| 📦 库存预警分析 | `/app/ai/stock-alerts` | 低库存物料 + AI 补货数量建议 |
| 👤 客户信用评估 | `/app/ai/customer-credit` | AI 评级 A/B/C/D + 建议信用额度 |
| 🏭 供应商质量评分 | `/app/ai/supplier-quality` | 月度来料合格率 + 改进建议 |
| 📈 销售预测 | `/app/ai/sales-forecast` | 基于历史数据预测未来 3 个月 |
| 💰 生产成本分析 | `/app/ai/production-cost` | 计划 vs 实际成本差异归因 |
| ⚠️ 异常订单检测 | `/app/ai/anomaly-detection` | 金额异常/新客户首单自动标记 |
| 📅 智能排程建议 | `/app/ai/scheduling` | 待开工工单优先顺序建议 |
| 🛒 电商订单合并 | `/app/ai/ecommerce-merge` | 同买家多平台订单合并发货建议 |
| 📧 邮件智能回复 | `/app/ai/email-reply` | 询盘邮件 AI 生成报价回复草稿 |

> **说明**：所有 AI 调用直连对应厂商 API，ERP 系统不存储对话内容。API Key 在后台 `/admin/ai-settings` 中统一管理。

## 开发命令

```bash
# 单元/功能测试
php artisan test

# 全角色全页面冒烟测试
php artisan test --filter=SmokeTest

# 队列监听
php artisan queue:listen

# 清理缓存
php artisan cache:clear && php artisan config:clear && php artisan route:clear

# 重新缓存（生产）
php artisan config:cache && php artisan route:cache
```

## 安全建议

1. 生产环境设置 `APP_DEBUG=false`
2. 确保 `.env` 文件权限为 600
3. 使用 HTTPS
4. 定期更新依赖包：`composer update`
5. 配置 CORS 策略

## 许可证

MIT
