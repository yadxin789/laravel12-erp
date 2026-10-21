<aside class="w-64 bg-white border-r min-h-screen p-4">
    <nav class="space-y-2">
        <div class="font-semibold text-gray-500 mb-2 px-2 text-xs uppercase tracking-wider">业务概览</div>
        <a href="{{ route('app.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.dashboard') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">仪表盘</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">采购管理</div>
        <a href="{{ route('app.purchase.orders.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.purchase.orders.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">采购订单</a>
        <a href="{{ route('app.purchase.requisitions.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.purchase.requisitions.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">请购单</a>
        <a href="{{ route('app.purchase.receipts.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.purchase.receipts.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">收货管理</a>
        <a href="{{ route('app.purchase.returns.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.purchase.returns.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">采购退货</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">基础信息</div>
        <a href="{{ route('admin.suppliers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.suppliers.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">供应商信息</a>
        <a href="{{ route('admin.items.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.items.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">物料信息</a>
    </nav>
</aside>
