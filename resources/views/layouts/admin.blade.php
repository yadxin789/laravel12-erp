<!DOCTYPE html>
<html lang="zh-CN">
<head>
    @include('layouts.partials.head', ['title' => '系统管理'])
</head>
<body>
    @include('layouts.partials.navbar', ['brandName' => 'ERP企业资源管理系统 · 系统管理'])

    <div class="erp-shell">
        <aside class="erp-sidebar d-none d-lg-block">
            @include('partials.sidebar_admin')
        </aside>

        <div class="offcanvas offcanvas-start erp-offcanvas" tabindex="-1" id="erpSidebar" aria-labelledby="erpSidebarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="erpSidebarLabel"><i class="fa-solid fa-gear me-2"></i>系统管理</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="关闭"></button>
            </div>
            <div class="offcanvas-body pt-0 px-2">
                @include('partials.sidebar_admin')
            </div>
        </div>

        <main class="erp-main">
            @include('layouts.partials.flash')
            @yield('content')
            <div class="erp-footer text-center">ERP 企业资源管理系统 · {{ date('Y') }}</div>
        </main>
    </div>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
