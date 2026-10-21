<aside class="w-64 bg-white border-r min-h-screen p-4">
    <nav class="space-y-2">
        <div class="font-semibold text-gray-500 mb-2 px-2 text-xs uppercase tracking-wider">业务概览</div>
        <a href="{{ route('app.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.dashboard') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">仪表盘</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">只读查看</div>
        <a href="{{ route('app.sales.orders.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.sales.orders.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">销售订单</a>
        <a href="{{ route('app.purchase.orders.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.purchase.orders.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">采购订单</a>
        <a href="{{ route('app.production.orders.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.production.orders.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">生产工单</a>
        <a href="{{ route('app.inventory.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.inventory.index') && !request()->routeIs('app.inventory.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">库存查询</a>
        <a href="{{ route('app.quality.inspections.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.quality.inspections.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">检验记录</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">基础信息</div>
        <a href="{{ route('admin.customers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.customers.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">客户信息</a>
        <a href="{{ route('admin.suppliers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.suppliers.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">供应商信息</a>
        <a href="{{ route('admin.items.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.items.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">物料信息</a>
    </nav>
</aside>
