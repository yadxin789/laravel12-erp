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
@if(\App\Support\Menu::can('app.ai.stock-alerts'))
<a href="{{ route('app.ai.stock-alerts') }}" class="{{ $item(request()->routeIs('app.ai.stock-alerts')) }}">
    <i class="fa-solid fa-triangle-exclamation"></i>库存预警分析
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

<div class="{{ $section }}">库存管理</div>
@if(\App\Support\Menu::can('app.inventory.index'))
<a href="{{ route('app.inventory.index') }}" class="{{ $item(request()->routeIs('app.inventory.index')) }}">
    <i class="fa-solid fa-magnifying-glass"></i>库存查询
</a>
@endif
@if(\App\Support\Menu::can('app.inventory.transfer'))
<a href="{{ route('app.inventory.transfer') }}" class="{{ $item(request()->routeIs('app.inventory.transfer*')) }}">
    <i class="fa-solid fa-right-left"></i>库存调拨
</a>
@endif
@if(\App\Support\Menu::can('app.inventory.adjust'))
<a href="{{ route('app.inventory.adjust') }}" class="{{ $item(request()->routeIs('app.inventory.adjust*')) }}">
    <i class="fa-solid fa-sliders"></i>库存调整
</a>
@endif
@if(\App\Support\Menu::can('app.inventory.freeze'))
<a href="{{ route('app.inventory.freeze') }}" class="{{ $item(request()->routeIs('app.inventory.freeze*')) }}">
    <i class="fa-solid fa-snowflake"></i>冻结解冻
</a>
@endif
@if(\App\Support\Menu::can('app.inventory.stocktake'))
<a href="{{ route('app.inventory.stocktake') }}" class="{{ $item(request()->routeIs('app.inventory.stocktake*')) }}">
    <i class="fa-solid fa-clipboard-check"></i>库存盘点
</a>
@endif
@if(\App\Support\Menu::can('app.inventory.trace'))
<a href="{{ route('app.inventory.trace') }}" class="{{ $item(request()->routeIs('app.inventory.trace*')) }}">
    <i class="fa-solid fa-code-branch"></i>批次追踪
</a>
@endif
@if(\App\Support\Menu::can('app.inventory.transactions'))
<a href="{{ route('app.inventory.transactions') }}" class="{{ $item(request()->routeIs('app.inventory.transactions')) }}">
    <i class="fa-solid fa-arrows-turn-to-dots"></i>库存流水
</a>
@endif
@if(\App\Support\Menu::can('app.inventory.logistics'))
<a href="{{ route('app.inventory.logistics') }}" class="{{ $item(request()->routeIs('app.inventory.logistics*')) }}">
    <i class="fa-solid fa-truck-fast"></i>物流查询
</a>
@endif

<div class="{{ $section }}">收货管理</div>
@if(\App\Support\Menu::can('app.purchase.receipts.index'))
<a href="{{ route('app.purchase.receipts.index') }}" class="{{ $item(request()->routeIs('app.purchase.receipts.*')) }}">
    <i class="fa-solid fa-box-open"></i>采购收货
</a>
@endif
@if(\App\Support\Menu::can('app.purchase.orders.index'))
<a href="{{ route('app.purchase.orders.index') }}" class="{{ $item(request()->routeIs('app.purchase.orders.*')) }}">
    <i class="fa-solid fa-file-invoice"></i>采购订单
</a>
@endif

<div class="{{ $section }}">生产协作</div>
@if(\App\Support\Menu::can('app.production.issues.index'))
<a href="{{ route('app.production.issues.index') }}" class="{{ $item(request()->routeIs('app.production.issues.*')) }}">
    <i class="fa-solid fa-dolly"></i>发料出库
</a>
@endif
@if(\App\Support\Menu::can('app.production.receipts.index'))
<a href="{{ route('app.production.receipts.index') }}" class="{{ $item(request()->routeIs('app.production.receipts.*')) }}">
    <i class="fa-solid fa-warehouse"></i>成品入库确认
</a>
@endif

<div class="{{ $section }}">基础信息</div>
@if(\App\Support\Menu::can('app.masterdata.items.index'))
<a href="{{ route('app.masterdata.items.index') }}" class="{{ $item(request()->routeIs('app.masterdata.items.*')) }}">
    <i class="fa-solid fa-box"></i>物料信息
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

<div class="{{ $section }}">报表与审批</div>
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
