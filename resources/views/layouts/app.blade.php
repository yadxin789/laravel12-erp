<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ERP系统')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100">
    <nav class="bg-gray-800 text-white">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-xl font-bold">ERP系统</a>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <span>{{ auth()->user()->name }}</span>
                        <a href="{{ route('notifications.index') }}" class="relative text-gray-300 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @php $unreadCount = app(\App\Services\NotificationService::class)->unreadCount(auth()->id()); @endphp
                            @if($unreadCount > 0)
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>
                            @endif
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-300 hover:text-white">退出</button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <aside class="w-64 bg-gray-200 min-h-screen p-4">
            <nav class="space-y-2">
                <a href="{{ route('app.dashboard') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">总览仪表盘</a>

                <div class="font-semibold text-gray-700 mb-2 mt-4">主数据</div>
                @if(Route::has('admin.items'))
                    <a href="{{ route('admin.items') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">物料管理</a>
                    <a href="{{ route('admin.customers') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">客户管理</a>
                    <a href="{{ route('admin.suppliers') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">供应商管理</a>
                    <a href="{{ route('admin.warehouses') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">仓库管理</a>
                @else
                    <span class="block px-3 py-2 text-gray-500">（主数据需管理员权限）</span>
                @endif

                <div class="font-semibold text-gray-700 mb-2 mt-4">销售管理</div>
                <a href="{{ route('app.sales.orders.index') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">销售订单</a>
                <a href="{{ route('app.sales.shipments.index') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">发货单</a>
                <a href="{{ route('app.sales.quotations.index') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">报价单</a>

                <div class="font-semibold text-gray-700 mb-2 mt-4">采购管理</div>
                <a href="{{ route('app.purchase.orders.index') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">采购订单</a>
                <a href="{{ route('app.purchase.receipts.index') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">收货单</a>
                <a href="{{ route('app.purchase.requisitions.index') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">请购单</a>

                <div class="font-semibold text-gray-700 mb-2 mt-4">生产管理</div>
                <a href="{{ route('app.production.orders.index') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">生产工单</a>
                <a href="{{ route('app.production.issues.index') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">领料单</a>
                <a href="{{ route('app.production.receipts.index') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">成品入库</a>

                <div class="font-semibold text-gray-700 mb-2 mt-4">库存管理</div>
                <a href="{{ route('app.inventory.index') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">库存查询</a>
                <a href="{{ route('app.inventory.trace') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">批次追溯</a>
                <a href="{{ route('app.inventory.stocktake') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">库存盘点</a>

                <div class="font-semibold text-gray-700 mb-2 mt-4">质量管理</div>
                <a href="{{ route('app.quality.inspections.index') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">检验管理</a>
                <a href="{{ route('app.quality.reports') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">质量报表</a>

                <div class="font-semibold text-gray-700 mb-2 mt-4">财务 / CRM</div>
                <a href="{{ route('app.finance.invoices') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">客户发票</a>
                <a href="{{ route('app.finance.bills') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">供应商账单</a>
                <a href="{{ route('app.crm.leads') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">线索/商机</a>

                <div class="font-semibold text-gray-700 mb-2 mt-4">分析 / 系统</div>
                <a href="{{ route('app.reports.index') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">报表中心</a>
                <a href="{{ route('app.ai.analysis-center') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">AI 分析中心</a>
                <a href="{{ route('app.approvals.my-tasks') }}" class="block px-3 py-2 bg-white rounded hover:bg-gray-300">我的审批待办</a>
            </nav>
        </aside>

        <main class="flex-1 p-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    @livewireScripts
</body>
</html>
