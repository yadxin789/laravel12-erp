<aside class="w-64 bg-white border-r min-h-screen p-4">
    <nav class="space-y-2">
        <div class="font-semibold text-gray-500 mb-2 px-2 text-xs uppercase tracking-wider">业务概览</div>
        <a href="{{ route('app.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.dashboard') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">仪表盘</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">库存管理</div>
        <a href="{{ route('app.inventory.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.inventory.index') && !request()->routeIs('app.inventory.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">库存查询</a>
        <a href="{{ route('app.inventory.transfer') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.inventory.transfer*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">库存调拨</a>
        <a href="{{ route('app.inventory.adjust') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.inventory.adjust*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">库存调整</a>
        <a href="{{ route('app.inventory.freeze') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.inventory.freeze*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">冻结解冻</a>
        <a href="{{ route('app.inventory.stocktake') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.inventory.stocktake*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">库存盘点</a>
        <a href="{{ route('app.inventory.trace') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.inventory.trace*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">批次追踪</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">收货管理</div>
        <a href="{{ route('app.purchase.receipts.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.purchase.receipts.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">采购收货</a>
        <a href="{{ route('app.purchase.orders.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.purchase.orders.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">采购订单</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">生产协作</div>
        <a href="{{ route('app.production.issues.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.production.issues.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">发料出库</a>
        <a href="{{ route('app.production.receipts.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.production.receipts.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">成品入库确认</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">基础信息</div>
        <a href="{{ route('admin.items.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.items.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">物料信息</a>
        <a href="{{ route('admin.warehouses.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.warehouses.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">仓库信息</a>
        <a href="{{ route('admin.boms.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.boms.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">BOM清单</a>
    </nav>
</aside>
