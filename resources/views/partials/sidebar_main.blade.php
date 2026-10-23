@php
    $section = 'erp-nav-section';
    $item = fn (bool $active) => $active ? 'active' : '';
@endphp

<div class="{{ $section }} mt-0">业务概览</div>
@if(\App\Support\Menu::can('app.dashboard'))
<a href="{{ route('app.dashboard') }}" class="{{ $item(request()->routeIs('app.dashboard')) }}">
    <i class="fa-solid fa-gauge-high"></i>仪表盘
</a>
@endif

<div class="{{ $section }}">智能助手</div>
@if(\App\Support\Menu::can('app.ai.chat'))
<a href="{{ route('app.ai.chat') }}" class="{{ $item(request()->routeIs('app.ai.chat*')) }}">
    <i class="fa-solid fa-robot"></i>AI 聊天
</a>
@endif
@if(\App\Support\Menu::can('app.ai.analysis-center'))
<a href="{{ route('app.ai.analysis-center') }}" class="{{ $item(request()->routeIs('app.ai.analysis*')) }}">
    <i class="fa-solid fa-wand-magic-sparkles"></i>智能分析中心
</a>
@endif

<div class="{{ $section }}">协作沟通</div>
@if(\App\Support\Menu::can('app.chat'))
<a href="{{ route('app.chat') }}" class="{{ $item(request()->routeIs('app.chat')) }}">
    <i class="fa-solid fa-comments"></i>群聊
</a>
@endif
@if(\App\Support\Menu::can('app.profile.edit'))
<a href="{{ route('app.profile.edit') }}" class="{{ $item(request()->routeIs('app.profile.*')) }}">
    <i class="fa-solid fa-id-card"></i>个人资料
</a>
@endif

<div class="{{ $section }}">销售管理</div>
@if(\App\Support\Menu::can('app.sales.orders.index'))
<a href="{{ route('app.sales.orders.index') }}" class="{{ $item(request()->routeIs('app.sales.orders.*')) }}">
    <i class="fa-solid fa-file-invoice"></i>销售订单
</a>
@endif
@if(\App\Support\Menu::can('app.sales.quotations.index'))
<a href="{{ route('app.sales.quotations.index') }}" class="{{ $item(request()->routeIs('app.sales.quotations.*')) }}">
    <i class="fa-solid fa-file-lines"></i>报价单
</a>
@endif
@if(\App\Support\Menu::can('app.sales.shipments.index'))
<a href="{{ route('app.sales.shipments.index') }}" class="{{ $item(request()->routeIs('app.sales.shipments.*')) }}">
    <i class="fa-solid fa-truck"></i>发货管理
</a>
@endif
@if(\App\Support\Menu::can('app.sales.returns.index'))
<a href="{{ route('app.sales.returns.index') }}" class="{{ $item(request()->routeIs('app.sales.returns.*')) }}">
    <i class="fa-solid fa-rotate-left"></i>销售退货
</a>
@endif

<div class="{{ $section }}">采购管理</div>
@if(\App\Support\Menu::can('app.purchase.orders.index'))
<a href="{{ route('app.purchase.orders.index') }}" class="{{ $item(request()->routeIs('app.purchase.orders.*')) }}">
    <i class="fa-solid fa-file-invoice"></i>采购订单
</a>
@endif
@if(\App\Support\Menu::can('app.purchase.requisitions.index'))
<a href="{{ route('app.purchase.requisitions.index') }}" class="{{ $item(request()->routeIs('app.purchase.requisitions.*')) }}">
    <i class="fa-solid fa-clipboard"></i>请购单
</a>
@endif
@if(\App\Support\Menu::can('app.purchase.receipts.index'))
<a href="{{ route('app.purchase.receipts.index') }}" class="{{ $item(request()->routeIs('app.purchase.receipts.*')) }}">
    <i class="fa-solid fa-box-open"></i>收货管理
</a>
@endif
@if(\App\Support\Menu::can('app.purchase.returns.index'))
<a href="{{ route('app.purchase.returns.index') }}" class="{{ $item(request()->routeIs('app.purchase.returns.*')) }}">
    <i class="fa-solid fa-rotate-left"></i>采购退货
</a>
@endif

<div class="{{ $section }}">生产管理</div>
@if(\App\Support\Menu::can('app.production.orders.index'))
<a href="{{ route('app.production.orders.index') }}" class="{{ $item(request()->routeIs('app.production.orders.*')) }}">
    <i class="fa-solid fa-industry"></i>生产工单
</a>
@endif
@if(\App\Support\Menu::can('app.production.plans.index'))
<a href="{{ route('app.production.plans.index') }}" class="{{ $item(request()->routeIs('app.production.plans.*')) }}">
    <i class="fa-solid fa-diagram-project"></i>生产计划
</a>
@endif
@if(\App\Support\Menu::can('app.production.issues.index'))
<a href="{{ route('app.production.issues.index') }}" class="{{ $item(request()->routeIs('app.production.issues.*')) }}">
    <i class="fa-solid fa-dolly"></i>领料管理
</a>
@endif
@if(\App\Support\Menu::can('app.production.receipts.index'))
<a href="{{ route('app.production.receipts.index') }}" class="{{ $item(request()->routeIs('app.production.receipts.*')) }}">
    <i class="fa-solid fa-warehouse"></i>成品入库
</a>
@endif

<div class="{{ $section }}">库存管理</div>
@if(\App\Support\Menu::can('app.inventory.index'))
<a href="{{ route('app.inventory.index') }}" class="{{ $item(request()->routeIs('app.inventory.index')) }}">
    <i class="fa-solid fa-boxes-stacked"></i>库存查询
</a>
@endif
@if(\App\Support\Menu::can('app.inventory.transfer'))
<a href="{{ route('app.inventory.transfer') }}" class="{{ $item(request()->routeIs('app.inventory.transfer*')) }}">
    <i class="fa-solid fa-right-left"></i>库存调拨
</a>
@endif
@if(\App\Support\Menu::can('app.inventory.trace'))
<a href="{{ route('app.inventory.trace') }}" class="{{ $item(request()->routeIs('app.inventory.trace*')) }}">
    <i class="fa-solid fa-code-branch"></i>批次追溯
</a>
@endif
@if(\App\Support\Menu::can('app.inventory.logistics'))
<a href="{{ route('app.inventory.logistics') }}" class="{{ $item(request()->routeIs('app.inventory.logistics*')) }}">
    <i class="fa-solid fa-truck-fast"></i>物流查询
</a>
@endif

<div class="{{ $section }}">质量管理</div>
@if(\App\Support\Menu::can('app.quality.inspections.index'))
<a href="{{ route('app.quality.inspections.index') }}" class="{{ $item(request()->routeIs('app.quality.inspections.*')) }}">
    <i class="fa-solid fa-clipboard-check"></i>检验管理
</a>
@endif
@if(\App\Support\Menu::can('app.quality.defects.index'))
<a href="{{ route('app.quality.defects.index') }}" class="{{ $item(request()->routeIs('app.quality.defects.*')) }}">
    <i class="fa-solid fa-bug"></i>缺陷管理
</a>
@endif
@if(\App\Support\Menu::can('app.quality.reports'))
<a href="{{ route('app.quality.reports') }}" class="{{ $item(request()->routeIs('app.quality.reports')) }}">
    <i class="fa-solid fa-chart-column"></i>质量报表
</a>
@endif

<div class="{{ $section }}">财务与 CRM</div>
@if(\App\Support\Menu::can('app.finance.invoices'))
<a href="{{ route('app.finance.invoices') }}" class="{{ $item(request()->routeIs('app.finance.invoices*')) }}">
    <i class="fa-solid fa-file-invoice-dollar"></i>客户发票
</a>
@endif
@if(\App\Support\Menu::can('app.finance.bills'))
<a href="{{ route('app.finance.bills') }}" class="{{ $item(request()->routeIs('app.finance.bills*')) }}">
    <i class="fa-solid fa-money-bill"></i>供应商账单
</a>
@endif
@if(\App\Support\Menu::can('app.finance.payments'))
<a href="{{ route('app.finance.payments') }}" class="{{ $item(request()->routeIs('app.finance.payments*')) }}">
    <i class="fa-solid fa-cash-register"></i>收付款
</a>
@endif
@if(\App\Support\Menu::can('app.crm.leads'))
<a href="{{ route('app.crm.leads') }}" class="{{ $item(request()->routeIs('app.crm.leads*')) }}">
    <i class="fa-solid fa-user-plus"></i>销售线索
</a>
@endif
@if(\App\Support\Menu::can('app.crm.pipeline'))
<a href="{{ route('app.crm.pipeline') }}" class="{{ $item(request()->routeIs('app.crm.pipeline')) }}">
    <i class="fa-solid fa-filter"></i>销售漏斗
</a>
@endif

<div class="{{ $section }}">主数据</div>
@if(\App\Support\Menu::can('app.masterdata.items.index'))
<a href="{{ route('app.masterdata.items.index') }}" class="{{ $item(request()->routeIs('app.masterdata.items.*')) }}">
    <i class="fa-solid fa-box"></i>物料信息
</a>
@endif
@if(\App\Support\Menu::can('app.masterdata.customers.index'))
<a href="{{ route('app.masterdata.customers.index') }}" class="{{ $item(request()->routeIs('app.masterdata.customers.*')) }}">
    <i class="fa-solid fa-users"></i>客户信息
</a>
@endif
@if(\App\Support\Menu::can('app.masterdata.suppliers.index'))
<a href="{{ route('app.masterdata.suppliers.index') }}" class="{{ $item(request()->routeIs('app.masterdata.suppliers.*')) }}">
    <i class="fa-solid fa-truck-field"></i>供应商信息
</a>
@endif
@if(\App\Support\Menu::can('app.masterdata.warehouses.index'))
<a href="{{ route('app.masterdata.warehouses.index') }}" class="{{ $item(request()->routeIs('app.masterdata.warehouses.*')) }}">
    <i class="fa-solid fa-warehouse"></i>仓库信息
</a>
@endif
@if(\App\Support\Menu::can('app.masterdata.boms.index'))
<a href="{{ route('app.masterdata.boms.index') }}" class="{{ $item(request()->routeIs('app.masterdata.boms.*')) }}">
    <i class="fa-solid fa-diagram-project"></i>BOM 清单
</a>
@endif

<div class="{{ $section }}">扩展与报表</div>
@if(\App\Support\Menu::can('app.hr.employees.index'))
<a href="{{ route('app.hr.employees.index') }}" class="{{ $item(request()->routeIs('app.hr.employees.*')) }}">
    <i class="fa-solid fa-id-badge"></i>员工档案
</a>
@endif
@if(\App\Support\Menu::can('app.hr.payroll.index'))
<a href="{{ route('app.hr.payroll.index') }}" class="{{ $item(request()->routeIs('app.hr.payroll.*')) }}">
    <i class="fa-solid fa-money-check-dollar"></i>薪资工资单
</a>
@endif
@if(\App\Support\Menu::can('app.hr.performances.index'))
<a href="{{ route('app.hr.performances.index') }}" class="{{ $item(request()->routeIs('app.hr.performances.*')) }}">
    <i class="fa-solid fa-ranking-star"></i>绩效评估
</a>
@endif
@if(\App\Support\Menu::can('app.hr.recruitments.index'))
<a href="{{ route('app.hr.recruitments.index') }}" class="{{ $item(request()->routeIs('app.hr.recruitments.*')) }}">
    <i class="fa-solid fa-bullhorn"></i>招聘需求
</a>
@endif
@if(\App\Support\Menu::can('app.hr.applications.index'))
<a href="{{ route('app.hr.applications.index') }}" class="{{ $item(request()->routeIs('app.hr.applications.*')) }}">
    <i class="fa-solid fa-users-viewfinder"></i>应聘者
</a>
@endif
@if(\App\Support\Menu::can('app.projects.index'))
<a href="{{ route('app.projects.index') }}" class="{{ $item(request()->routeIs('app.projects.*')) }}">
    <i class="fa-solid fa-folder-open"></i>项目管理
</a>
@endif
@if(\App\Support\Menu::can('app.assets.index'))
<a href="{{ route('app.assets.index') }}" class="{{ $item(request()->routeIs('app.assets.*')) }}">
    <i class="fa-solid fa-building-columns"></i>固定资产
</a>
@endif
@if(\App\Support\Menu::can('app.ecommerce.index'))
<a href="{{ route('app.ecommerce.index') }}" class="{{ $item(request()->routeIs('app.ecommerce.*')) }}">
    <i class="fa-solid fa-shop"></i>电商订单
</a>
@endif
@if(\App\Support\Menu::can('app.reports.index'))
<a href="{{ route('app.reports.index') }}" class="{{ $item(request()->routeIs('app.reports.*')) }}">
    <i class="fa-solid fa-chart-simple"></i>报表中心
</a>
@endif
@if(\App\Support\Menu::can('app.approvals.my-tasks'))
<a href="{{ route('app.approvals.my-tasks') }}" class="{{ $item(request()->routeIs('app.approvals.*')) }}">
    <i class="fa-solid fa-list-check"></i>我的审批待办
</a>
@endif
