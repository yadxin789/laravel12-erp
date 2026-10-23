# 电子工厂 ERP 系统

基于 Laravel 12 的电子制造企业资源计划（ERP）系统，覆盖销售、采购、生产、库存、质量、财务、CRM、AI 智能化、协作沟通（群聊/私聊）、物流快递等全流程。

## 技术栈

- **后端**: Laravel 12 + PHP 8.4
- **数据库**: MySQL 8.4（推荐）/ SQLite 3（开发备选）
- **前端**: Blade + Bootstrap 5 + FontAwesome（本地静态资源 `public/css`、`public/js`，**无需 npm 构建**）
- **权限**: Spatie/Laravel-Permission (RBAC) + 11 个 Policy
- **API**: Laravel Sanctum
- **队列**: Database Driver

> 2026-10-22 起前端样式已由 Tailwind 全面切换为 Bootstrap 5（`resources/views` 内不再有 Tailwind 类），
> `package.json` 中的 `tailwindcss` / `vite` 仅作历史保留，页面渲染不依赖 `npm run build`。

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
| **HR** | 员工档案、考勤记录、薪资工资单、绩效评估、招聘（需求 + 应聘者，录用可转员工档案） |
| **项目管理** | 项目、任务、工时统计 |
| **资产管理** | 固定资产登记、月折旧、累计折旧、净值自动计算 |
| **电商对接** | 淘宝/京东/亚马逊/拼多多订单同步 |
| **AI 能力** | 多供应商（DeepSeek/通义千问/豆包/元宝）**同时配置**、模型目录管理（按供应商同步/自定义/设默认/是否开放用户）、多会话 AI 聊天（模型切换、附件、图片理解、Markdown 渲染、导出）、发票 OCR、自然语言查库存、AI 审批建议 |
| **AI 智能分析中心** | 经营日报、库存预警分析（AI 补货建议）、客户信用评估、供应商质量评分、销售预测、生产成本差异分析、异常订单检测、智能排程建议、电商订单合并、邮件智能回复 |
| **协作沟通** | 全员群聊（文件/图片、撤回、5 秒轮询）、一对一私聊（离线自动转站内消息、进入会话自动已读）、「清空我的记录」只影响本人视图 |
| **个人中心** | 头像上传（jpg/png/gif/webp ≤2MB）、5 套系统默认头像一键选择、个人资料（电话/岗位/签名）、在线状态与最近活跃 |
| **物流快递** | 后台开通引导页（快递100/快递鸟/菜鸟 申请链接）+ 通道与快递公司设置、仓库物流查询（10 家快递、轨迹时间线、运单登记）、物流状态变化站内通知 |
| **组织主数据** | 部门管理（上下级/排序/停用）、岗位管理（职级/编制/默认授予角色） |
| **系统管理** | 用户、角色（中文岗位名）、权限（中文名 + 模块分组）、审批流（按岗位角色配置）、审计日志、通知中心（含物流/私聊类型）、编号序列、附件管理 |

### 岗位 → 角色 → 权限 模型

```
模块(Module)   = 功能边界（菜单与路由的组织方式）
角色(Role)     = 权限载体：可继承、可叠加、一人多角色 —— 决定"能做什么"
岗位(Position) = 组织编制：所属部门、职级、编制人数 —— 决定"是谁、归谁管"
```

- 岗位**不直接授权**，只设置「任职默认授予角色」；员工档案关联岗位 + 系统账号后，**保存即自动授予该角色**，调岗自动换权
- 角色中文岗位名在 `App\Support\RoleCatalog` 统一维护，后台角色管理可覆盖 `display_name`，用户管理/审批流/AI 分流均按中文岗位名展示
- 12 个角色对应 11 个业务模块，每个模块都有明确的责任岗位

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

# 安装依赖（前端样式为本地静态资源，npm install / npm run build 可跳过）
composer install

# 配置环境
cp .env.example .env
php artisan key:generate

# 创建数据库（MySQL）
mysql -u root -p -e "CREATE DATABASE laravel12_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 修改 .env 中 DB_* 段，填写数据库连接信息

# 运行迁移和填充
php artisan migrate --seed

# 创建存储链接（头像、聊天附件依赖它）
php artisan storage:link

# 启动开发服务器
php artisan serve
```

### 默认账号

| 角色 | 账号 | 密码 | 说明 |
|------|------|------|------|
| admin | admin@admin.com | 123456 | 系统管理员，后台管理（不参与业务） |
| 销售经理 | sales@demo.com | 123456 | 销售订单/报价/发货/退货 + CRM + 客户发票 |
| 采购经理 | purchase@demo.com | 123456 | 采购模块全部操作 |
| 生产经理 | production@demo.com | 123456 | 生产模块全部操作 + 库存查询 |
| 仓库经理 | warehouse@demo.com | 123456 | 库存模块全部操作 + 收货/领料 |
| 质量经理 | quality@demo.com | 123456 | 质量模块全部操作 |
| 财务 | accounting@demo.com | 123456 | 财务模块 + 各模块只读 |
| 查看员 | viewer@demo.com | 123456 | 全模块只读 |
| 人事专员 | hr@demo.com | 123456 | 员工档案 + 考勤 + 薪资 + 绩效 + 招聘 |
| 项目经理 | project@demo.com | 123456 | 项目 / 任务 / 工时 |
| 资产管理员 | asset@demo.com | 123456 | 固定资产台账 + 折旧 |
| 电商运营 | ec@demo.com | 123456 | 电商订单归集 + 同步 |

> 所有演示账号共享密码 `123456`；登录页可一键填充。每个角色都有对应的演示用户，可真实进入该角色的操作界面完成演示。

## 演示数据

运行 `php artisan migrate --seed` 后会创建以下演示数据：

| 数据 | 数量 |
|------|------|
| 角色 | 12 |
| 用户 | 12 |
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
| 部门 / 岗位 | 10 / 17 |
| 员工 / 考勤 | 8 / 40（岗位自动关联 5 人） |
| 项目 / 任务 / 工时 | 2 / 5 / 5 |
| 资产 | 5 |
| 电商订单 | 6（4 个平台） |
| 工资单 / 绩效 / 招聘需求 / 应聘者 | 24 / 16 / 3 / 8 |
| 通知 | 18 |
| AI 设置 | 1（DeepSeek 默认） |

种子按顺序执行：`RolePermissionSeeder` → `DemoDataSeeder` → `ExtendedDemoDataSeeder` → `DepartmentPositionSeeder` → `HrExtDataSeeder`，全部幂等（已存在则跳过）。

> AI 供应商/模型、快递通道目录会在访问设置页时按内置目录自动补齐（`syncFromCatalog`）；
> 聊天记录、物流运单无演示数据，登录后在群聊 / 仓库物流查询页自行产生。

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
│   │   ├── Inventory/  # 库存 API 控制器（JSON）
│   │   └── Api/        # 库存查询等 API（受 Policy 保护）
│   └── Middleware/
├── Listeners/          # 事件订阅器（审计日志 + 通知）
├── Models/
│   ├── MasterData/ Sales/ Purchasing/ Production/ Inventory/ Quality/ Finance/ Crm/ …
│   ├── System/         # AI（供应商/模型/会话/消息/附件）、通知、审批流
│   ├── Chat/           # 群聊私聊消息、清空状态
│   └── Logistics/      # 快递通道/公司、运单、轨迹
├── Policies/           # 11 个授权策略（AuthServiceProvider 中注册）
├── Services/           # 11 个服务层（含 AiService、AiToolService、NotificationService、LogisticsService）
└── Support/            # 工具类（NumberGenerator、Money、AuditLogger）
database/
├── migrations/         # 81 个迁移
└── seeders/            # 角色权限 + 基础演示 + 扩展演示
resources/
└── views/
    ├── layouts/        # admin / app / business 三套布局（Bootstrap 5）
    ├── components/     # 可复用 Blade 组件
    ├── partials/       # 13 个角色侧边栏
    ├── app/            # 业务视图（含 ai/chat、chat、profile、inventory/logistics）
    ├── admin/          # 后台视图（含 ai-settings、logistics）
    └── masterdata/     # 主数据视图
routes/
├── web.php             # Web 路由（321 条，含 AI/聊天/物流）
├── api.php             # API 路由
└── auth.php            # 认证路由
tests/
└── Feature/            # Feature/Controller 测试（AuthTest、CrmTest、SmokeTest）
```

### 存储盘

| 盘 | 路径 | 用途 |
|----|------|------|
| `public` | `storage/app/public` | 物料图片、单据附件、头像（`avatars/`） |
| `chat` | `storage/app/public/chat-files` | 群聊/私聊附件（公开访问，需 `storage:link`） |
| `ai_chat` | `storage/app/ai-chat` | AI 聊天附件（私有，经控制器预览/下载） |

默认头像为 5 个内置 SVG：`public/avatars/defaults/avatar1~5.svg`，无需上传即可选择。

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

> **说明**：API Key 在后台 `/admin/ai-settings` 中统一管理，支持多家供应商并存（每家独立 Key、独立开关、设默认、单家连通性测试）。

### AI 聊天（`/app/ai/chat`）

| 能力 | 说明 |
|------|------|
| 多会话 | 新建 / 重命名 / 置顶 / 清空 / 删除 / 导出 Markdown |
| 模型切换 | 顶部下拉只列出「供应商已开通 且 已开放给用户」的模型 |
| 附件 | 图片走视觉模型（base64 `image_url`），txt/csv 抽取原文拼进提示词；最多 5 个 × 10MB |
| 渲染 | 服务端 `Str::markdown(html_input: strip)`，消息落库（`ai_conversations` / `ai_messages` / `ai_attachments`） |

### 协作沟通

| 页面 | 路由 | 说明 |
|------|------|------|
| 群聊 | `/app/chat` | 全员可发；成员列表显示在线状态、岗位，点击成员直接发起私聊 |
| 私聊 | `/app/chat/{user}` | 左侧联系人按最后消息排序；对方离线时落一条 `private_message` 站内通知 |
| 附件下载 | `/app/chat/files/{message}` | 群聊/私聊附件，存储盘 `chat`（`storage/app/public/chat-files`） |

### 物流快递

| 页面 | 路由 | 说明 |
|------|------|------|
| 开通引导 | `/admin/logistics/guide` | 三步走：选通道 → 申请密钥 → 粘贴测试 |
| 物流设置 | `/admin/logistics/settings` | ① 查询通道（AppKey/AppSecret、设默认、测试、开关）② 10 家快递公司启用清单 ③ 高级参数 |
| 运单列表 | `/admin/logistics/shipments` | 状态筛选、刷新轨迹 |
| 仓库物流查询 | `/app/inventory/logistics` | 选公司 + 运单号查询、轨迹时间线、登记新运单 |
| 运单详情 | `/app/inventory/logistics/{id}` | 运单信息 + 完整轨迹 |

查询服务三档回退，保证任何环境可用：

1. 已配置密钥 → 聚合通道正式 API（快递100 签名 `param+key+customer`）
2. 未配置密钥 → 快递100 **免费公共查询接口**（无需鉴权）
3. 网络不可用 → 演示轨迹，页面顶部明确标注「演示数据」

状态变化（签收 / 异常 / 退回）通过 `LogisticsService::notifyStatusChanged()` 发送 `logistics` 类型站内消息，并额外通知 `warehouse_manager` 角色。

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
