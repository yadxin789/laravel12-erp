<aside class="w-64 bg-white border-r min-h-screen p-4">
    <nav class="space-y-2">
        <div class="font-semibold text-gray-500 mb-2 px-2 text-xs uppercase tracking-wider">财务报表</div>
        <a href="{{ route('app.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.dashboard') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">经营仪表盘</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">销售数据</div>
        <a href="{{ route('app.sales.orders.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.sales.orders.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">销售订单（只读）</a>
        <a href="{{ route('app.sales.shipments.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.sales.shipments.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">发货记录</a>
        <a href="{{ route('app.sales.returns.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.sales.returns.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">退货记录</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">采购数据</div>
        <a href="{{ route('app.purchase.orders.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.purchase.orders.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">采购订单（只读）</a>
        <a href="{{ route('app.purchase.receipts.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.purchase.receipts.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">收货记录</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">库存与生产</div>
        <a href="{{ route('app.inventory.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.inventory.index') && !request()->routeIs('app.inventory.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">库存查询（只读）</a>
        <a href="{{ route('app.production.orders.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.production.orders.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">生产工单（只读）</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">质量成本</div>
        <a href="{{ route('app.quality.inspections.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.quality.inspections.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">检验记录（只读）</a>
        <a href="{{ route('app.quality.reports') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.quality.reports') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">质量报表</a>
    </nav>
</aside>
