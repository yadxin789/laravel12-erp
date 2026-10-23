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

<div class="{{ $section }}">人力资源</div>
@if(\App\Support\Menu::can('app.hr.employees.index'))
<a href="{{ route('app.hr.employees.index') }}" class="{{ $item(request()->routeIs('app.hr.employees.*')) }}">
    <i class="fa-solid fa-users"></i>员工档案
</a>
@endif
@if(\App\Support\Menu::can('app.hr.attendances.index'))
<a href="{{ route('app.hr.attendances.index') }}" class="{{ $item(request()->routeIs('app.hr.attendances.*')) }}">
    <i class="fa-solid fa-calendar-check"></i>考勤管理
</a>
@endif
@if(\App\Support\Menu::can('app.hr.payroll.index'))
<a href="{{ route('app.hr.payroll.index') }}" class="{{ $item(request()->routeIs('app.hr.payroll.*')) }}">
    <i class="fa-solid fa-money-check-dollar"></i>薪资管理
</a>
@endif
@if(\App\Support\Menu::can('app.hr.performances.index'))
<a href="{{ route('app.hr.performances.index') }}" class="{{ $item(request()->routeIs('app.hr.performances.*')) }}">
    <i class="fa-solid fa-ranking-star"></i>绩效考核
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

<div class="{{ $section }}">智能助手</div>
@if(\App\Support\Menu::can('app.ai.chat'))
<a href="{{ route('app.ai.chat') }}" class="{{ $item(request()->routeIs('app.ai.chat*')) }}">
    <i class="fa-solid fa-robot"></i>AI 聊天
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

<div class="{{ $section }}">基础信息</div>
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

<div class="{{ $section }}">报表中心</div>
@if(\App\Support\Menu::can('app.reports.index'))
<a href="{{ route('app.reports.index') }}" class="{{ $item(request()->routeIs('app.reports.*')) }}">
    <i class="fa-solid fa-chart-simple"></i>统计报表
</a>
@endif
