@php
    $section = 'erp-nav-section';
    $item = fn (bool $active) => $active ? 'active' : '';
@endphp

<div class="{{ $section }} mt-0">财务报表</div>
@if(\App\Support\Menu::can('app.dashboard'))
<a href="{{ route('app.dashboard') }}" class="{{ $item(request()->routeIs('app.dashboard')) }}">
    <i class="fa-solid fa-gauge-high"></i>经营仪表盘
</a>
@endif

<div class="{{ $section }}">智能助手</div>
@if(\App\Support\Menu::can('app.ai.chat'))
<a href="{{ route('app.ai.chat') }}" class="{{ $item(request()->routeIs('app.ai.chat*')) }}">
    <i class="fa-solid fa-robot"></i>AI 聊天
</a>
@endif
@if(\App\Support\Menu::can('app.ai.daily-report'))
<a href="{{ route('app.ai.daily-report') }}" class="{{ $item(request()->routeIs('app.ai.daily-report')) }}">
    <i class="fa-solid fa-newspaper"></i>经营日报
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

<div class="{{ $section }}">应收应付</div>
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
@if(\App\Support\Menu::can('app.finance.statements'))
<a href="{{ route('app.finance.statements') }}" class="{{ $item(request()->routeIs('app.finance.statements')) }}">
    <i class="fa-solid fa-scale-balanced"></i>往来对账单
</a>
@endif

<div class="{{ $section }}">销售数据</div>
@if(\App\Support\Menu::can('app.sales.orders.index'))
<a href="{{ route('app.sales.orders.index') }}" class="{{ $item(request()->routeIs('app.sales.orders.*')) }}">
    <i class="fa-solid fa-file-invoice"></i>销售订单
</a>
@endif
@if(\App\Support\Menu::can('app.sales.shipments.index'))
<a href="{{ route('app.sales.shipments.index') }}" class="{{ $item(request()->routeIs('app.sales.shipments.*')) }}">
    <i class="fa-solid fa-truck"></i>发货记录
</a>
@endif
@if(\App\Support\Menu::can('app.sales.returns.index'))
<a href="{{ route('app.sales.returns.index') }}" class="{{ $item(request()->routeIs('app.sales.returns.*')) }}">
    <i class="fa-solid fa-rotate-left"></i>退货记录
</a>
@endif

<div class="{{ $section }}">采购数据</div>
@if(\App\Support\Menu::can('app.purchase.orders.index'))
<a href="{{ route('app.purchase.orders.index') }}" class="{{ $item(request()->routeIs('app.purchase.orders.*')) }}">
    <i class="fa-solid fa-file-invoice"></i>采购订单
</a>
@endif
@if(\App\Support\Menu::can('app.purchase.receipts.index'))
<a href="{{ route('app.purchase.receipts.index') }}" class="{{ $item(request()->routeIs('app.purchase.receipts.*')) }}">
    <i class="fa-solid fa-box-open"></i>收货记录
</a>
@endif

<div class="{{ $section }}">库存与生产</div>
@if(\App\Support\Menu::can('app.inventory.index'))
<a href="{{ route('app.inventory.index') }}" class="{{ $item(request()->routeIs('app.inventory.index')) }}">
    <i class="fa-solid fa-boxes-stacked"></i>库存查询
</a>
@endif
@if(\App\Support\Menu::can('app.production.orders.index'))
<a href="{{ route('app.production.orders.index') }}" class="{{ $item(request()->routeIs('app.production.orders.*')) }}">
    <i class="fa-solid fa-industry"></i>生产工单
</a>
@endif

<div class="{{ $section }}">质量成本</div>
@if(\App\Support\Menu::can('app.quality.inspections.index'))
<a href="{{ route('app.quality.inspections.index') }}" class="{{ $item(request()->routeIs('app.quality.inspections.*')) }}">
    <i class="fa-solid fa-clipboard-check"></i>检验记录
</a>
@endif
@if(\App\Support\Menu::can('app.quality.reports'))
<a href="{{ route('app.quality.reports') }}" class="{{ $item(request()->routeIs('app.quality.reports')) }}">
    <i class="fa-solid fa-chart-column"></i>质量报表
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
