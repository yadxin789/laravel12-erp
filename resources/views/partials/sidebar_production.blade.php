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
@if(\App\Support\Menu::can('app.ai.scheduling'))
<a href="{{ route('app.ai.scheduling') }}" class="{{ $item(request()->routeIs('app.ai.scheduling')) }}">
    <i class="fa-solid fa-calendar-days"></i>智能排程
</a>
@endif
@if(\App\Support\Menu::can('app.ai.production-cost'))
<a href="{{ route('app.ai.production-cost') }}" class="{{ $item(request()->routeIs('app.ai.production-cost')) }}">
    <i class="fa-solid fa-coins"></i>生产成本分析
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
@if(\App\Support\Menu::can('app.production.reports.index'))
<a href="{{ route('app.production.reports.index') }}" class="{{ $item(request()->routeIs('app.production.reports.*')) }}">
    <i class="fa-solid fa-clipboard-list"></i>生产汇报
</a>
@endif
@if(\App\Support\Menu::can('app.production.receipts.index'))
<a href="{{ route('app.production.receipts.index') }}" class="{{ $item(request()->routeIs('app.production.receipts.*')) }}">
    <i class="fa-solid fa-warehouse"></i>成品入库
</a>
@endif

<div class="{{ $section }}">库存协作</div>
@if(\App\Support\Menu::can('app.inventory.index'))
<a href="{{ route('app.inventory.index') }}" class="{{ $item(request()->routeIs('app.inventory.index')) }}">
    <i class="fa-solid fa-magnifying-glass"></i>库存查询
</a>
@endif
@if(\App\Support\Menu::can('app.inventory.trace'))
<a href="{{ route('app.inventory.trace') }}" class="{{ $item(request()->routeIs('app.inventory.trace*')) }}">
    <i class="fa-solid fa-code-branch"></i>批次追溯
</a>
@endif

<div class="{{ $section }}">技术数据</div>
@if(\App\Support\Menu::can('app.masterdata.boms.index'))
<a href="{{ route('app.masterdata.boms.index') }}" class="{{ $item(request()->routeIs('app.masterdata.boms.*')) }}">
    <i class="fa-solid fa-diagram-project"></i>BOM 清单
</a>
@endif
@if(\App\Support\Menu::can('app.masterdata.routings.index'))
<a href="{{ route('app.masterdata.routings.index') }}" class="{{ $item(request()->routeIs('app.masterdata.routings.*')) }}">
    <i class="fa-solid fa-route"></i>工艺路线
</a>
@endif
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
