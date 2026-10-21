<aside class="w-64 bg-white border-r min-h-screen p-4">
    <nav class="space-y-2">
        <div class="font-semibold text-gray-500 mb-2 px-2 text-xs uppercase tracking-wider">业务概览</div>
        <a href="{{ route('app.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.dashboard') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">仪表盘</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">生产管理</div>
        <a href="{{ route('app.production.orders.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.production.orders.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">生产工单</a>
        <a href="{{ route('app.production.plans.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.production.plans.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">生产计划</a>
        <a href="{{ route('app.production.issues.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.production.issues.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">领料管理</a>
        <a href="{{ route('app.production.reports.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.production.reports.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">生产汇报</a>
        <a href="{{ route('app.production.receipts.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.production.receipts.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">成品入库</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">技术数据</div>
        <a href="{{ route('admin.boms.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.boms.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">BOM清单</a>
        <a href="{{ route('admin.routings.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.routings.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">工艺路线</a>
        <a href="{{ route('admin.items.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.items.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">物料信息</a>
        <a href="{{ route('admin.warehouses.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.warehouses.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">仓库信息</a>
    </nav>
</aside>
