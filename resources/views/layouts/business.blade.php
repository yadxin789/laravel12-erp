<!DOCTYPE html>
<html lang="zh-CN">
<head>
    @include('layouts.partials.head', ['title' => '业务系统'])
</head>
<body>
    @include('layouts.partials.navbar', ['brandName' => '电子工厂ERP系统'])

    @php
        $roleName = auth()->user()?->roles?->first()?->name ?? 'viewer';
        $sidebarMap = [
            'sales_manager' => 'sales',
            'purchase_manager' => 'purchase',
            'production_manager' => 'production',
            'warehouse_manager' => 'warehouse',
            'quality_manager' => 'quality',
            'hr_specialist' => 'hr',
            'project_manager' => 'project',
            'asset_manager' => 'asset',
            'ecommerce_operator' => 'ecommerce',
        ];
        $sidebarName = $sidebarMap[$roleName] ?? $roleName;
        $sidebarView = 'partials.sidebar_' . $sidebarName;
        if (! \Illuminate\Support\Facades\View::exists($sidebarView)) {
            $sidebarView = 'partials.sidebar_viewer';
        }
    @endphp

    <div class="erp-shell">
        <aside class="erp-sidebar d-none d-lg-block">
            @include($sidebarView)
        </aside>

        <div class="offcanvas offcanvas-start erp-offcanvas" tabindex="-1" id="erpSidebar" aria-labelledby="erpSidebarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="erpSidebarLabel"><i class="fa-solid fa-cubes me-2"></i>业务菜单</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="关闭"></button>
            </div>
            <div class="offcanvas-body pt-0 px-2">
                @include($sidebarView)
            </div>
        </div>

        <main class="erp-main">
            @include('layouts.partials.flash')
            @yield('content')
            <div class="erp-footer text-center">电子工厂ERP系统 · {{ date('Y') }}</div>
        </main>
    </div>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
