<aside class="w-64 bg-white border-r min-h-screen p-4">
    <nav class="space-y-2">
        <div class="font-semibold text-gray-500 mb-2 px-2 text-xs uppercase tracking-wider">业务概览</div>
        <a href="{{ route('app.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.dashboard') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">仪表盘</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">质量管理</div>
        <a href="{{ route('app.quality.inspections.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.quality.inspections.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">检验管理</a>
        <a href="{{ route('app.quality.defects.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.quality.defects.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">缺陷管理</a>
        <a href="{{ route('app.quality.reports') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.quality.reports') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">质量报表</a>

        <div class="font-semibold text-gray-500 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">关联数据</div>
        <a href="{{ route('app.purchase.orders.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.purchase.orders.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">采购订单（来料检验参考）</a>
        <a href="{{ route('app.production.orders.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('app.production.orders.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">生产工单（过程/出货检验参考）</a>
        <a href="{{ route('admin.items.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.items.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}">物料规格</a>
    </nav>
</aside>
