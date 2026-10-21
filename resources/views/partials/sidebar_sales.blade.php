<aside class="w-64 bg-white border-r min-h-screen p-4">
    <nav class="space-y-2">
        <div class="font-semibold text-gray-500 mb-2 px-2 text-xs uppercase tracking-wider">业务概览</div>
        <a href="{{ route('app.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.dashboard') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">仪表盘</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">销售管理</div>
        <a href="{{ route('app.sales.orders.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.sales.orders.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">销售订单</a>
        <a href="{{ route('app.sales.quotations.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.sales.quotations.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">报价单</a>
        <a href="{{ route('app.sales.shipments.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.sales.shipments.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">发货管理</a>
        <a href="{{ route('app.sales.returns.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.sales.returns.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">销售退货</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">基础信息</div>
        <a href="{{ route('admin.customers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.customers.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">客户信息</a>
        <a href="{{ route('admin.items.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.items.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">产品信息</a>
    </nav>
</aside>
