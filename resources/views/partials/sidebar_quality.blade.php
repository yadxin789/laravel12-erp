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
@if(\App\Support\Menu::can('app.ai.supplier-quality'))
<a href="{{ route('app.ai.supplier-quality') }}" class="{{ $item(request()->routeIs('app.ai.supplier-quality')) }}">
    <i class="fa-solid fa-ranking-star"></i>供应商质量评分
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

<div class="{{ $section }}">关联数据</div>
@if(\App\Support\Menu::can('app.purchase.orders.index'))
<a href="{{ route('app.purchase.orders.index') }}" class="{{ $item(request()->routeIs('app.purchase.orders.*')) }}">
    <i class="fa-solid fa-file-invoice"></i>采购订单
</a>
@endif
@if(\App\Support\Menu::can('app.production.orders.index'))
<a href="{{ route('app.production.orders.index') }}" class="{{ $item(request()->routeIs('app.production.orders.*')) }}">
    <i class="fa-solid fa-industry"></i>生产工单
</a>
@endif
@if(\App\Support\Menu::can('app.masterdata.items.index'))
<a href="{{ route('app.masterdata.items.index') }}" class="{{ $item(request()->routeIs('app.masterdata.items.*')) }}">
    <i class="fa-solid fa-box"></i>物料规格
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
