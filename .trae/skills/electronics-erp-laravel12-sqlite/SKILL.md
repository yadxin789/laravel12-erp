---
name: electronics-erp-laravel12-sqlite
description: >根据SKILL.MD文件内容完成全部开发任务。
---

name: electronics-erp-laravel12-sqlite
description: >
  用于设计、开发、测试和交付基于 Laravel 12 + SQLite 的电子厂 ERP 系统。
  AI Agent 必须按照本 Skill 完成项目分析、数据库设计、模块开发、权限控制、
  库存事务、生产管理、质量追溯、测试和部署文档，禁止跳过核心业务规则。
version: 1.0.0
language: zh-CN
framework: Laravel 12
runtime: PHP 8.3+
database: SQLite 3
frontend: Blade + Livewire 3 + Alpine.js + Tailwind CSS
project_type: monolithic-modular-erp
---

# 电子厂 ERP Laravel 12 开发 Skill

## 1. Skill 目标

本 Skill 用于指导 AI Agent 直接执行电子厂 ERP 系统的：

- 需求分析；
- 项目结构设计；
- 数据库迁移；
- 模型和关系设计；
- 权限和角色设计；
- 销售管理；
- 采购管理；
- 仓库与库存管理；
- BOM 和工艺管理；
- 生产计划与生产工单；
- IQC、IPQC、FQC、OQC；
- 批次、序列号和产品追溯；
- 报表和驾驶舱；
- API、队列和定时任务；
- 自动化测试；
- 部署、备份和上线文档。

AI Agent 必须优先保证：

1. 数据一致性；
2. 库存准确性；
3. 单据状态完整；
4. 权限后端有效；
5. 业务流程可追溯；
6. 代码可测试；
7. 数据库可迁移；
8. 生产环境可维护。

---

# 2. 执行模式

AI Agent 执行任务时必须遵循以下顺序：

```text
检查项目环境
  ↓
识别已有代码和数据库
  ↓
建立或确认模块边界
  ↓
设计数据库迁移
  ↓
实现 Enum、Model 和关系
  ↓
实现 Form Request
  ↓
实现 Policy 和权限
  ↓
实现 Service 和事务
  ↓
实现 Controller / Livewire
  ↓
实现 Blade 页面
  ↓
实现报表和查询
  ↓
编写测试
  ↓
执行迁移、测试和代码检查
  ↓
生成交付文档
        
    
name: electronics-erp-laravel12-sqlite
description: >
用于设计、开发、测试和交付基于 Laravel 12 + SQLite 的电子厂 ERP 系统。
AI Agent 必须按照本 Skill 完成项目分析、数据库设计、模块开发、权限控制、
库存事务、生产管理、质量追溯、测试和部署文档，禁止跳过核心业务规则。
version: 1.0.0
language: zh-CN
framework: Laravel 12
runtime: PHP 8.3+
database: SQLite 3
frontend: Blade + Livewire 3 + Alpine.js + Tailwind CSS
project_type: monolithic-modular-erp
电子厂 ERP Laravel 12 开发 Skill
1. Skill 目标
本 Skill 用于指导 AI Agent 直接执行电子厂 ERP 系统的：

需求分析；
项目结构设计；
数据库迁移；
模型和关系设计；
权限和角色设计；
销售管理；
采购管理；
仓库与库存管理；
BOM 和工艺管理；
生产计划与生产工单；
IQC、IPQC、FQC、OQC；
批次、序列号和产品追溯；
报表和驾驶舱；
API、队列和定时任务；
自动化测试；
部署、备份和上线文档。
AI Agent 必须优先保证：

数据一致性；
库存准确性；
单据状态完整；
权限后端有效；
业务流程可追溯；
代码可测试；
数据库可迁移；
生产环境可维护。
2. 执行模式
AI Agent 执行任务时必须遵循以下顺序：

        
text

        

            

        

        
            
检查项目环境
  ↓
识别已有代码和数据库
  ↓
建立或确认模块边界
  ↓
设计数据库迁移
  ↓
实现 Enum、Model 和关系
  ↓
实现 Form Request
  ↓
实现 Policy 和权限
  ↓
实现 Service 和事务
  ↓
实现 Controller / Livewire
  ↓
实现 Blade 页面
  ↓
实现报表和查询
  ↓
编写测试
  ↓
执行迁移、测试和代码检查
  ↓
生成交付文档
        
    
不得直接从页面开始开发，不得绕过数据库设计和业务规则。

3. 项目执行原则
3.1 必须遵守
使用 Laravel 12 推荐方式；
使用 PHP 8.3+ 语法；
使用 Eloquent ORM；
使用 Form Request 进行请求验证；
使用 Policy 进行后端授权；
使用 Service 承载复杂业务逻辑；
使用数据库事务处理跨模型业务；
使用 Enum 管理核心业务状态；
使用软删除管理主数据；
使用审计日志记录重要操作；
使用库存流水记录所有库存变化；
使用 Migration 管理数据库结构；
使用自动化测试验证核心流程。
3.2 禁止行为
禁止：

在 Controller 中直接修改库存；
在 Blade 页面中执行数据库查询；
仅通过前端隐藏按钮实现权限控制；
直接修改生产数据库；
删除已审核或已完成业务单据；
删除库存流水；
使用浮点数保存金额；
使用 FLOAT 保存价格或金额；
绕过 Service 直接更新库存余额；
绕过状态机修改单据状态；
审核后直接覆盖订单、BOM 或工单；
在事务中执行外部 HTTP 请求；
在代码中写死客户、供应商或仓库名称；
未测试就声明任务完成。
4. 执行前环境检查
AI Agent 开始开发前，必须执行并记录以下检查。

4.1 检查命令
        
bash

        

            

        

        
            
pwd
ls -la
find . -maxdepth 2 -type f | sort | head -200

php -v
composer -V
node -v
npm -v

php artisan --version
php artisan about
        
    
检查 SQLite：

        
bash

        

            

        

        
            
php -m | grep -i sqlite
php -r "var_dump(extension_loaded('pdo_sqlite'));"
php -r "var_dump(extension_loaded('sqlite3'));"
        
    
检查 Git：

        
bash

        

            

        

        
            
git status --short
git branch --show-current
        
    
4.2 检查结果处理
如果项目不是 Laravel 12：

不得直接假设项目结构；
先输出兼容性分析；
如果用户允许升级，再执行升级；
如果不允许升级，必须使用当前项目版本兼容方式；
不得删除现有业务代码。
如果缺少 SQLite 扩展：

输出明确错误；
不得继续执行数据库测试；
提供安装建议；
可以继续生成迁移和代码，但必须标记为“未完成运行验证”。
如果项目目录存在未提交修改：

不得覆盖用户已有修改；
先执行 git diff --stat；
对冲突文件进行保护；
修改前记录受影响文件。
5. 项目初始化标准
5.1 Laravel 项目要求
如果项目为空目录，初始化：

        
bash

        

            

        

        
            
composer create-project laravel/laravel .
        
    
确认 Laravel 版本：

        
bash

        

            

        

        
            
php artisan --version
        
    
如果项目已存在：

        
bash

        

            

        

        
            
composer install
npm install
        
    
5.2 SQLite 初始化
创建数据库：

        
bash

        

            

        

        
            
mkdir -p database
touch database/database.sqlite
        
    
.env 使用：

        
env

        

            

        

        
            
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=database
FILESYSTEM_DISK=local
        
    
生产环境必须：

        
env

        

            

        

        
            
APP_ENV=production
APP_DEBUG=false
        
    
5.3 推荐依赖
根据项目现状选择安装：

        
bash

        

            

        

        
            
composer require livewire/livewire
composer require spatie/laravel-permission
composer require laravel/sanctum
composer require --dev pestphp/pest
composer require --dev phpstan/phpstan
composer require --dev laravel/pint
        
    
如果项目已经存在同类依赖，禁止重复安装。

6. 目录结构
AI Agent 应优先建立以下目录：

        
text

        

            

        

        
            
app/
├── Enums/
├── Events/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   ├── MasterData/
│   │   ├── Sales/
│   │   ├── Purchasing/
│   │   ├── Inventory/
│   │   ├── Production/
│   │   └── Quality/
│   ├── Requests/
│   └── Middleware/
├── Livewire/
├── Models/
│   ├── MasterData/
│   ├── Sales/
│   ├── Purchasing/
│   ├── Inventory/
│   ├── Production/
│   └── Quality/
├── Policies/
├── Services/
│   ├── InventoryService.php
│   ├── SalesOrderService.php
│   ├── PurchaseOrderService.php
│   ├── ProductionOrderService.php
│   ├── QualityService.php
│   ├── MRPService.php
│   └── TraceabilityService.php
└── Support/
    ├── NumberGenerator.php
    ├── AuditLogger.php
    └── Money.php
        
    
如果已有目录结构，优先兼容现有项目，不得机械覆盖。

7. 模块开发顺序
必须按照以下顺序开发：

        
text

        

            

        

        
            
1. 系统参数
2. 用户、部门、角色、权限
3. 单位和物料分类
4. 物料
5. 客户和供应商
6. 仓库和库位
7. 工作中心和生产线
8. BOM
9. 工艺路线
10. 库存余额和库存流水
11. 采购申请和采购订单
12. 采购收货和入库
13. 销售报价和销售订单
14. 销售出库
15. 生产计划和生产工单
16. 生产领料、退料和补料
17. 工序报工
18. 完工入库
19. IQC、IPQC、FQC、OQC
20. 不良、返工、报废和让步接收
21. 批次追溯
22. 报表和驾驶舱
23. MRP
24. 条码、设备、成本和外部接口
        
    
如果用户指定某个模块优先开发，仍必须检查其依赖模块是否已经存在。

8. 数据库设计标准
8.1 主数据表
必须支持以下表：

        
text

        

            

        

        
            
departments
units
item_categories
items
item_suppliers
item_customers
customers
suppliers
warehouses
locations
work_centers
production_lines
        
    
8.2 销售表
        
text

        

            

        

        
            
quotations
quotation_lines
sales_orders
sales_order_lines
sales_order_changes
shipments
shipment_lines
sales_returns
sales_return_lines
        
    
8.3 采购表
        
text

        

            

        

        
            
purchase_requisitions
purchase_requisition_lines
purchase_orders
purchase_order_lines
receipts
receipt_lines
purchase_returns
purchase_return_lines
supplier_evaluations
        
    
8.4 生产表
        
text

        

            

        

        
            
bom_headers
bom_lines
routing_headers
routing_operations
production_plans
production_plan_lines
production_orders
production_order_lines
material_issues
material_issue_lines
material_returns
material_return_lines
production_reports
production_receipts
rework_orders
production_losses
material_substitutions
        
    
8.5 质量表
        
text

        

            

        

        
            
quality_standards
quality_inspections
quality_inspection_lines
defect_codes
defect_records
corrective_actions
customer_complaints
recall_orders
        
    
8.6 库存表
        
text

        

            

        

        
            
inventory_balances
inventory_transactions
inventory_reservations
inventory_freezes
stocktakes
stocktake_lines
        
    
8.7 系统表
        
text

        

            

        

        
            
users
departments
roles
permissions
audit_logs
approval_instances
approval_records
number_sequences
system_settings
attachments
idempotency_records
        
    
9. 字段和精度标准
9.1 金额
禁止：

        
text

        

            

        

        
            
FLOAT
DOUBLE
        
    
推荐：

        
text

        

            

        

        
            
unit_price_minor INTEGER
amount_minor INTEGER
tax_amount_minor INTEGER
total_amount_minor INTEGER
        
    
金额换算：

        
text

        

            

        

        
            
12.35 元 = 1235 分
        
    
9.2 数量
如果业务数量只使用整数，使用：

        
text

        

            

        

        
            
INTEGER
        
    
如果需要小数，使用整数缩放：

        
text

        

            

        

        
            
quantity_value INTEGER
quantity_scale INTEGER
        
    
实际数量：

        
text

        

            

        

        
            
实际数量 = quantity_value / quantity_scale
        
    
9.3 通用状态
所有重要单据必须包含：

        
text

        

            

        

        
            
status
created_by
approved_by
approved_at
created_at
updated_at
remark
        
    
主数据建议增加：

        
text

        

            

        

        
            
deleted_at
        
    
10. Enum 要求
核心状态必须创建 Enum，不得在业务代码中大量散落字符串。

建议创建：

        
text

        

            

        

        
            
ApprovalStatus.php
InventoryTransactionType.php
InventoryQualityStatus.php
SalesOrderStatus.php
PurchaseOrderStatus.php
ProductionOrderStatus.php
QualityResult.php
InspectionType.php
        
    
示例：

        
php

        

            

        

        
            
<?php

namespace App\Enums;

enum ProductionOrderStatus: string
{
    case Draft = 'draft';
    case Planned = 'planned';
    case Released = 'released';
    case MaterialShortage = 'material_shortage';
    case Ready = 'ready';
    case InProgress = 'in_progress';
    case Paused = 'paused';
    case Completed = 'completed';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
}
        
    
11. 单据状态机
11.1 通用状态
        
text

        

            

        

        
            
草稿
  ↓
待审核
  ↓
已审核
  ↓
执行中
  ↓
部分完成
  ↓
已完成
  ↓
已关闭
        
    
11.2 销售订单
        
text

        

            

        

        
            
draft
  → submitted
  → approved
  → partially_delivered
  → fully_delivered
  → closed
        
    
允许：

        
text

        

            

        

        
            
submitted → draft
approved → cancelled
approved → change_pending
        
    
11.3 采购订单
        
text

        

            

        

        
            
draft
  → submitted
  → approved
  → partially_received
  → fully_received
  → closed
        
    
11.4 生产工单
        
text

        

            

        

        
            
draft
  → released
  → material_shortage
  → ready
  → in_progress
  → completed
  → closed
        
    
允许：

        
text

        

            

        

        
            
in_progress → paused
in_progress → rework
approved → cancelled
        
    
11.5 状态修改要求
每次状态变更必须校验：

当前状态；
操作权限；
业务前置条件；
关联单据是否完成；
是否存在未处理异常；
是否需要审批；
是否需要电子签名。
12. 库存核心规则
12.1 库存写入唯一入口
所有库存操作必须通过：

        
text

        

            

        

        
            
App\Services\InventoryService
        
    
至少实现：

        
text

        

            

        

        
            
receive()
issue()
transfer()
reserve()
release()
adjust()
freeze()
unfreeze()
return()
getAvailable()
traceBatch()
        
    
12.2 入库流程
        
text

        

            

        

        
            
开始事务
  ↓
锁定库存余额
  ↓
创建或查询库存余额
  ↓
保存入库数量
  ↓
写入库存流水
  ↓
更新来源单据
  ↓
记录审计日志
  ↓
提交事务
        
    
12.3 出库流程
        
text

        

            

        

        
            
开始事务
  ↓
锁定库存余额
  ↓
计算可用库存
  ↓
检查批次
  ↓
检查质量状态
  ↓
检查可用数量
  ↓
扣减库存
  ↓
写入库存流水
  ↓
更新来源单据
  ↓
提交事务
        
    
12.4 库存质量状态
        
text

        

            

        

        
            
pending       待检
qualified     合格
rejected      不合格
frozen        冻结
conditional   让步接收
        
    
只有 qualified 状态的库存可以正常生产领料和销售出库。

12.5 库存禁止事项
禁止：

        
php

        

            

        

        
            
$balance->quantity -= $quantity;
$balance->save();
        
    
禁止：

        
php

        

            

        

        
            
DB::table('inventory_balances')->update(...);
        
    
必须：

        
php

        

            

        

        
            
app(InventoryService::class)->issue($data);
        
    
13. 物料、BOM 和工艺规则
13.1 物料规则
编码唯一；
停用物料不可用于新单据；
已有业务记录的物料不得物理删除；
批次管理物料必须录入批次；
序列号管理物料必须录入序列号；
物料必须绑定基本单位；
采购单位和库存单位可以不同，但必须设置换算关系。
13.2 BOM 规则
BOM 必须有版本；
BOM 必须有生效日期；
BOM 必须审核；
工单创建时保存 BOM 版本；
已使用 BOM 不能删除；
BOM 变更必须创建新版本；
BOM 子件必须是有效物料；
BOM 不得出现循环引用；
BOM 数量必须大于零；
损耗率不能小于零；
替代料必须建立结构化关系。
13.3 工艺规则
工序号必须唯一；
工序必须属于有效工艺路线；
工作中心必须有效；
需要报工的工序必须允许操作员报工；
需要检验的工序必须关联质量标准；
外协工序必须关联外协供应商或外协流程。
14. 销售模块执行清单
AI Agent 实现销售模块时必须完成：

        
text

        

            

        

        
            
[ ] 客户管理
[ ] 报价单主表
[ ] 报价单明细
[ ] 报价审核
[ ] 报价转订单
[ ] 销售订单主表
[ ] 销售订单明细
[ ] 订单审核
[ ] 订单交期
[ ] 库存预留
[ ] 拣货单
[ ] 销售出库
[ ] 出货批次
[ ] 销售退货
[ ] 订单变更
[ ] 订单关闭
[ ] 订单执行报表
[ ] 权限
[ ] 审计日志
[ ] 自动化测试
        
    
销售出库前必须检查：

订单状态；
客户状态；
物料状态；
可用库存；
批次；
质量状态；
出货数量；
客户交期；
是否超过订单数量。
15. 采购模块执行清单
        
text

        

            

        

        
            
[ ] 供应商管理
[ ] 采购申请
[ ] MRP 来源申请
[ ] 询价
[ ] 供应商报价
[ ] 采购订单
[ ] 采购订单审核
[ ] 采购收货
[ ] 分批收货
[ ] 短收处理
[ ] 多收处理
[ ] 来料待检
[ ] IQC 关联
[ ] 合格入库
[ ] 不合格隔离
[ ] 采购退货
[ ] 采购订单关闭
[ ] 供应商交期统计
[ ] 供应商质量统计
[ ] 权限
[ ] 审计日志
[ ] 自动化测试
        
    
采购收货时必须校验：

采购订单状态；
供应商状态；
物料状态；
到货数量；
是否超过订单数量；
批次；
质量状态；
采购价格；
交货日期。
16. 仓库模块执行清单
        
text

        

            

        

        
            
[ ] 仓库
[ ] 库位
[ ] 库存余额
[ ] 库存流水
[ ] 采购入库
[ ] 生产入库
[ ] 销售出库
[ ] 生产领料
[ ] 生产退料
[ ] 调拨
[ ] 盘点
[ ] 库存调整
[ ] 库存冻结
[ ] 库存解冻
[ ] 批次查询
[ ] 库龄分析
[ ] 呆滞料分析
[ ] 低库存预警
[ ] 待检库存
[ ] 不合格库存
[ ] 权限
[ ] 审计日志
[ ] 自动化测试
        
    
库存调整必须包含：

        
text

        

            

        

        
            
调整原因
调整数量
调整前数量
调整后数量
操作人
审核人
来源单据
        
    
17. 生产模块执行清单
        
text

        

            

        

        
            
[ ] 生产计划
[ ] 生产计划明细
[ ] 生产工单
[ ] 工单审核
[ ] BOM 版本锁定
[ ] 工艺路线锁定
[ ] 物料需求计算
[ ] 工单欠料分析
[ ] 生产领料
[ ] 生产退料
[ ] 补料
[ ] 换料
[ ] 工序报工
[ ] 良品报工
[ ] 不良报工
[ ] 返工
[ ] 报废
[ ] 完工入库
[ ] 成品批次
[ ] 工单关闭
[ ] 生产报表
[ ] 权限
[ ] 审计日志
[ ] 自动化测试
        
    
工单关闭前必须检查：

未完成领料；
未完成退料；
未完成报工；
未处理不良；
未完成检验；
未入库数量；
未关闭返工单；
未处理批次关系。
18. 质量模块执行清单
        
text

        

            

        

        
            
[ ] IQC
[ ] IPQC
[ ] FQC
[ ] OQC
[ ] 检验标准
[ ] 检验项目
[ ] 检验模板
[ ] 不良代码
[ ] 不良记录
[ ] 隔离库存
[ ] 返工
[ ] 返修
[ ] 报废
[ ] 让步接收
[ ] 纠正预防措施
[ ] 供应商质量评价
[ ] 客诉
[ ] 批次追溯
[ ] 质量报表
[ ] 权限
[ ] 审计日志
[ ] 自动化测试
        
    
不合格物料必须进入：

        
text

        

            

        

        
            
不合格品仓
        
    
不得继续留在正常合格库存中。

19. MRP 初版规则
第一版 MRP 只需要实现：

销售订单需求；
生产计划需求；
BOM 展开；
当前库存；
已预留库存；
在途采购；
安全库存；
最小采购量；
建议采购量；
建议生产量；
缺料清单。
计算公式：

        
text

        

            

        

        
            
毛需求 =
成品需求量 × BOM 用量 × (1 + 损耗率)
        
    
        
text

        

            

        

        
            
净需求 =
毛需求 - 可用库存 - 在途数量 + 安全库存
        
    
        
text

        

            

        

        
            
建议采购量 =
max(净需求, 最小采购量)
        
    
第一版必须采用：

        
text

        

            

        

        
            
MRP 计算结果
  → 人工确认
  → 生成采购申请 / 生产工单
        
    
禁止第一版自动提交正式采购订单。

20. API 和接口执行清单
        
text

        

            

        

        
            
[ ] /api/v1/items
[ ] /api/v1/customers
[ ] /api/v1/suppliers
[ ] /api/v1/inventory/balances
[ ] /api/v1/inventory/transactions
[ ] /api/v1/inventory/receive
[ ] /api/v1/inventory/issue
[ ] /api/v1/sales/orders
[ ] /api/v1/purchasing/orders
[ ] /api/v1/production/orders
[ ] /api/v1/production/reports
[ ] Sanctum 认证
[ ] 权限校验
[ ] 幂等处理
[ ] 请求日志
[ ] 错误格式
[ ] 重试机制
        
    
统一错误响应格式：

        
json

        

            

        

        
            
{
  "success": false,
  "message": "可用库存不足",
  "code": "INSUFFICIENT_INVENTORY",
  "errors": {},
  "request_id": "REQ-202607130001"
}
        
    
21. 前端页面执行标准
21.1 页面必须具备
页面标题；
面包屑；
查询条件；
新增按钮；
导出按钮；
分页；
状态标签；
权限控制；
成功提示；
错误提示；
表单字段验证；
空数据提示；
加载状态；
防重复提交。
21.2 列表页
列表页必须支持：

编号查询；
日期查询；
状态筛选；
客户或供应商筛选；
物料筛选；
分页；
导出当前筛选结果；
查看详情；
根据权限显示操作按钮。
21.3 表单页
表单页必须支持：

保存草稿；
提交审核；
返回列表；
明细新增；
明细删除；
数量校验；
金额计算；
单位显示；
批次选择；
库位选择；
业务错误提示。
22. 权限实施要求
权限必须至少区分：

        
text

        

            

        

        
            
view
create
update
delete
submit
approve
reject
reverse
cancel
export
print
adjust
view-price
view-cost
        
    
示例：

        
text

        

            

        

        
            
sales.order.view
sales.order.create
sales.order.update
sales.order.submit
sales.order.approve
sales.order.cancel
sales.order.export
        
    
每个写操作必须同时通过：

路由或中间件检查；
Controller 或 Form Request 检查；
Policy 检查；
Service 业务规则检查。
23. 测试要求
23.1 最低测试范围
必须测试：

        
text

        

            

        

        
            
[ ] 物料编码唯一
[ ] 客户编码唯一
[ ] 供应商编码唯一
[ ] 订单编号生成
[ ] 采购收货
[ ] 采购入库
[ ] 库存入库
[ ] 库存出库
[ ] 库存不足
[ ] 待检库存禁止出库
[ ] 不合格库存禁止出库
[ ] 销售订单超量出货禁止
[ ] 采购订单超量收货禁止
[ ] 生产领料
[ ] 生产退料
[ ] 完工入库
[ ] 工单关闭
[ ] 质量判定
[ ] 批次正向追溯
[ ] 批次反向追溯
[ ] 越权访问
[ ] 审核后不可直接修改
[ ] 事务失败自动回滚
        
    
23.2 测试命令
        
bash

        

            

        

        
            
php artisan test
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
vendor/bin/pint --test
vendor/bin/phpstan analyse
        
    
如果项目使用 Pest：

        
bash

        

            

        

        
            
vendor/bin/pest
        
    
23.3 测试失败处理
测试失败时，AI Agent 必须：

读取完整错误信息；
定位失败文件和行号；
分析根因；
修复代码；
重新执行失败测试；
再执行完整测试；
记录修复内容。
不得通过删除测试或降低断言来“修复”失败。

24. 数据库检查命令
每次数据库结构变更后执行：

        
bash

        

            

        

        
            
php artisan migrate:fresh --seed
php artisan migrate:status
php artisan db:show
php artisan db:table inventory_balances
php artisan db:table inventory_transactions
        
    
生产环境禁止随意执行：

        
bash

        

            

        

        
            
php artisan migrate:fresh
        
    
生产迁移只能使用：

        
bash

        

            

        

        
            
php artisan migrate --force
        
    
执行生产迁移前必须先备份数据库。

25. 代码质量要求
每次提交前执行：

        
bash

        

            

        

        
            
vendor/bin/pint
vendor/bin/phpstan analyse
php artisan test
        
    
代码要求：

类名使用 PascalCase；
方法名使用 camelCase；
数据库字段使用 snake_case；
Enum 使用明确命名；
Service 方法命名表达业务动作；
Controller 保持精简；
复杂查询放入 Query Object 或 Repository；
业务异常使用自定义 Exception；
不使用无意义的 mixed；
不使用大段重复代码；
不在 View 中写业务逻辑；
不在 Model 中堆积跨模块流程。
26. 日志和监控
必须监控：

登录失败；
权限变更；
库存扣减失败；
数据库锁等待；
队列失败；
定时任务失败；
外部接口失败；
库存负数；
审批流程卡住；
报表生成失败；
数据库空间不足。
健康检查接口：

        
text

        

            

        

        
            
GET /health
        
    
检查：

应用是否正常；
数据库是否可连接；
数据库是否可写；
存储目录是否可写；
队列是否正常；
磁盘空间；
最近备份时间。
27. 部署和备份要求
27.1 部署前
        
text

        

            

        

        
            
[ ] 运行测试
[ ] 运行 Pint
[ ] 运行 PHPStan
[ ] 检查环境变量
[ ] 检查 APP_DEBUG
[ ] 备份数据库
[ ] 检查存储权限
[ ] 检查队列
[ ] 检查定时任务
[ ] 检查健康检查
        
    
27.2 部署命令
        
bash

        

            

        

        
            
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm ci
npm run build
        
    
27.3 备份要求
每日自动备份 SQLite 文件；
备份附件目录；
备份 .env 的安全副本；
备份迁移版本；
备份到独立存储；
定期测试恢复；
不得只保留服务器本地备份。
28. AI Agent 交付格式
AI Agent 每完成一个模块，必须输出以下内容：

        
text

        

            

        

        
            
模块名称：
完成状态：
新增文件：
修改文件：
数据库迁移：
新增模型：
新增 Enum：
新增 Service：
新增 Request：
新增 Policy：
新增路由：
新增页面：
新增测试：
执行过的命令：
测试结果：
已知问题：
后续建议：
        
    
示例：

        
text

        

            

        

        
            
模块名称：库存入库

完成状态：已完成

新增文件：
- app/Services/InventoryService.php
- app/Models/InventoryBalance.php
- app/Models/InventoryTransaction.php
- database/migrations/xxxx_create_inventory_balances_table.php
- database/migrations/xxxx_create_inventory_transactions_table.php
- tests/Feature/Inventory/ReceiptTest.php

执行过的命令：
- php artisan migrate:fresh --seed
- php artisan test --filter=Inventory
- vendor/bin/pint --test

测试结果：
- 通过：12
- 失败：0

已知问题：
- 暂不支持 FIFO 自动批次分配。

后续建议：
- 增加批次自动分配和库存预留。
        
    
29. 完成判定标准
AI Agent 只有在以下条件全部满足时，才可以将模块标记为“完成”：

数据层
Migration 已完成；
Migration 可执行；
Migration 可回滚；
外键和索引已配置；
唯一约束已配置；
字段类型符合规范。
业务层
正常流程已实现；
异常流程已实现；
状态流转已实现；
业务事务已实现；
错误提示已实现；
审计日志已实现。
权限层
查看权限；
新增权限；
编辑权限；
审核权限；
作废权限；
导出权限；
数据范围权限；
后端 Policy 校验。
页面层
列表页；
新增页；
编辑页；
详情页；
筛选；
分页；
表单验证；
成功提示；
错误提示；
防重复提交。
测试层
单元测试；
功能测试；
权限测试；
异常测试；
事务回滚测试；
完整测试通过。
文档层
模块说明；
数据表说明；
使用说明；
测试记录；
已知问题；
后续扩展建议。
30. 默认执行任务模板
当用户要求“开发电子厂 ERP 模块”时，AI Agent 应按照以下模板执行：

        
text

        

            

        

        
            
任务名称：{模块名称}

第一步：检查项目状态
- 检查 Laravel 版本；
- 检查 PHP 版本；
- 检查数据库；
- 检查已有模型、迁移、路由和页面；
- 检查 Git 工作区。

第二步：分析依赖
- 列出依赖模块；
- 判断哪些模块已经完成；
- 判断哪些模块需要补充；
- 禁止重复创建已有结构。

第三步：设计数据库
- 创建 Migration；
- 增加字段；
- 增加索引；
- 增加外键；
- 增加状态字段；
- 增加审计字段。

第四步：实现领域模型
- 创建 Enum；
- 创建 Model；
- 创建关系；
- 创建 Cast；
- 创建 Scope。

第五步：实现业务服务
- 创建 Service；
- 实现事务；
- 实现状态流转；
- 实现业务校验；
- 实现库存或金额处理；
- 实现审计日志。

第六步：实现请求和权限
- 创建 Form Request；
- 创建 Policy；
- 注册权限；
- 配置数据范围。

第七步：实现界面
- 创建 Controller 或 Livewire；
- 创建列表页；
- 创建表单页；
- 创建详情页；
- 增加筛选、分页、提示和导出。

第八步：实现测试
- 编写单元测试；
- 编写功能测试；
- 编写权限测试；
- 编写异常测试；
- 编写事务回滚测试。

第九步：执行验证
- 执行 Migration；
- 执行 Seeder；
- 执行测试；
- 执行 Pint；
- 执行 PHPStan；
- 修复所有阻塞性错误。

第十步：输出交付报告
- 列出新增和修改文件；
- 列出执行命令；
- 列出测试结果；
- 列出已知问题；
- 列出后续建议。
        
    
31. 默认模块验收标准
31.1 销售订单
        
text

        

            

        

        
            
创建销售订单
  → 提交审核
  → 审核通过
  → 预留库存或生成生产需求
  → 分批出货
  → 更新已交数量
  → 完成订单
  → 关闭订单
        
    
31.2 采购入库
        
text

        

            

        

        
            
创建采购订单
  → 审核采购订单
  → 收货
  → 生成待检库存
  → IQC 检验
  → 合格入库
  → 更新采购收货数量
  → 完成采购订单
        
    
31.3 生产工单
        
text

        

            

        

        
            
创建生产工单
  → 锁定 BOM
  → 锁定工艺路线
  → 检查欠料
  → 下达工单
  → 生产领料
  → 工序报工
  → 制程检验
  → 完工入库
  → 成品检验
  → 关闭工单
        
    
31.4 批次追溯
        
text

        

            

        

        
            
成品批次
  → 生产工单
  → 工序报工
  → 原材料批次
  → 供应商
  → IQC
  → 客户订单
  → 销售出库
        
    
32. 最终项目完成标准
整个 ERP 项目完成时必须满足：

        
text

        

            

        

        
            
[ ] 销售、采购、仓库、生产和质量流程打通
[ ] 物料、客户、供应商和仓库主数据统一
[ ] 所有库存变动有流水
[ ] 库存余额和库存流水一致
[ ] 重要单据有状态机
[ ] 审核后单据不可直接覆盖
[ ] 批次可以正向追溯
[ ] 批次可以反向追溯
[ ] 权限和数据范围生效
[ ] 审计日志完整
[ ] 关键报表可以生成
[ ] 自动化测试通过
[ ] 备份可以恢复
[ ] 部署文档完成
[ ] 用户操作手册完成
[ ] 试点运行通过
[ ] 业务负责人验收通过
        
    
33. 最终实施路线
        
text

        

            

        

        
            
统一主数据
    ↓
建立库存事实
    ↓
打通采购与仓库
    ↓
打通销售与出货
    ↓
接入生产工单
    ↓
完善质量管理
    ↓
实现批次追溯
    ↓
建设经营报表
    ↓
试点上线
    ↓
全厂上线
    ↓
扩展 MRP、条码、设备、成本和财务
        
    
AI Agent 必须始终以以下目标为准：

建立一个以订单为驱动、以库存为基础、以生产为核心、以质量为保障、以批次追溯为支撑，并且能够稳定运行、测试、备份和维护的电子厂 ERP 系统。