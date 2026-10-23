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

<div class="{{ $section }}">供应商与应付</div>
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

<div class="{{ $section }}">基础信息</div>
@if(\App\Support\Menu::can('app.masterdata.suppliers.index'))
<a href="{{ route('app.masterdata.suppliers.index') }}" class="{{ $item(request()->routeIs('app.masterdata.suppliers.*')) }}">
    <i class="fa-solid fa-truck-field"></i>供应商信息
</a>
@endif
@if(\App\Support\Menu::can('app.masterdata.items.index'))
<a href="{{ route('app.masterdata.items.index') }}" class="{{ $item(request()->routeIs('app.masterdata.items.*')) }}">
    <i class="fa-solid fa-box"></i>物料信息
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
