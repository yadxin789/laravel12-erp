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

<div class="{{ $section }}">客户与应收</div>
@if(\App\Support\Menu::can('app.crm.leads'))
<a href="{{ route('app.crm.leads') }}" class="{{ $item(request()->routeIs('app.crm.leads*')) }}">
    <i class="fa-solid fa-user-plus"></i>销售线索
</a>
@endif
@if(\App\Support\Menu::can('app.crm.opportunities'))
<a href="{{ route('app.crm.opportunities') }}" class="{{ $item(request()->routeIs('app.crm.opportunities*')) }}">
    <i class="fa-solid fa-handshake"></i>商机管理
</a>
@endif
@if(\App\Support\Menu::can('app.crm.pipeline'))
<a href="{{ route('app.crm.pipeline') }}" class="{{ $item(request()->routeIs('app.crm.pipeline')) }}">
    <i class="fa-solid fa-filter"></i>销售漏斗
</a>
@endif
@if(\App\Support\Menu::can('app.finance.invoices'))
<a href="{{ route('app.finance.invoices') }}" class="{{ $item(request()->routeIs('app.finance.invoices*')) }}">
    <i class="fa-solid fa-file-invoice-dollar"></i>客户发票
</a>
@endif

<div class="{{ $section }}">基础信息</div>
@if(\App\Support\Menu::can('app.masterdata.customers.index'))
<a href="{{ route('app.masterdata.customers.index') }}" class="{{ $item(request()->routeIs('app.masterdata.customers.*')) }}">
    <i class="fa-solid fa-users"></i>客户信息
</a>
@endif
@if(\App\Support\Menu::can('app.masterdata.items.index'))
<a href="{{ route('app.masterdata.items.index') }}" class="{{ $item(request()->routeIs('app.masterdata.items.*')) }}">
    <i class="fa-solid fa-box"></i>产品信息
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
