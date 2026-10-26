{{-- 运维监控四个页面的共用 Tab，保持一致的操作路径 --}}
@php
    $active = $active ?? '';
    $tabs = [
        ['route' => 'admin.monitor.system-status', 'label' => '系统工作状态', 'icon' => 'fa-heart-pulse'],
        ['route' => 'admin.monitor.scheduled-tasks', 'label' => '定时任务', 'icon' => 'fa-regular fa-clock'],
        ['route' => 'admin.monitor.queue', 'label' => '队列', 'icon' => 'fa-solid fa-layer-group'],
        ['route' => 'admin.monitor.auto-start', 'label' => '自启动程序', 'icon' => 'fa-solid fa-rocket'],
        ['route' => 'admin.system-health', 'label' => '告警与备份', 'icon' => 'fa-solid fa-bell'],
    ];
@endphp

<ul class="nav nav-pills mb-4 flex-wrap gap-1">
    @foreach($tabs as $tab)
        <li class="nav-item">
            <a href="{{ route($tab['route']) }}"
               class="nav-link {{ $active === $tab['route'] ? 'active' : '' }}">
                <i class="{{ $tab['icon'] }} me-1"></i>{{ $tab['label'] }}
            </a>
        </li>
    @endforeach
</ul>
