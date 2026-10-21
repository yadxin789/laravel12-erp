<aside class="w-64 bg-gray-800 text-white min-h-screen p-4">
    <nav class="space-y-2">
        <div class="font-semibold text-gray-400 mb-2 px-2 text-xs uppercase tracking-wider">系统管理</div>
        <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">系统概览</a>
        <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.users.*') ? 'bg-gray-700' : '' }}">用户管理</a>
        <a href="{{ route('admin.roles.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.roles.*') ? 'bg-gray-700' : '' }}">角色管理</a>
        <a href="{{ route('admin.permissions.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.permissions.*') ? 'bg-gray-700' : '' }}">权限管理</a>

        <div class="font-semibold text-gray-400 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">主数据维护</div>
        <a href="{{ route('admin.items.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.items.*') ? 'bg-gray-700' : '' }}">物料管理</a>
        <a href="{{ route('admin.customers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.customers.*') ? 'bg-gray-700' : '' }}">客户管理</a>
        <a href="{{ route('admin.suppliers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.suppliers.*') ? 'bg-gray-700' : '' }}">供应商管理</a>
        <a href="{{ route('admin.warehouses.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.warehouses.*') ? 'bg-gray-700' : '' }}">仓库管理</a>
        <a href="{{ route('admin.boms.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.boms.*') ? 'bg-gray-700' : '' }}">BOM管理</a>
        <a href="{{ route('admin.routings.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.routings.*') ? 'bg-gray-700' : '' }}">工艺路线</a>

        <div class="font-semibold text-gray-400 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">系统设置</div>
        <a href="{{ route('admin.settings.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.settings.*') ? 'bg-gray-700' : '' }}">系统设置</a>
        <a href="{{ route('admin.ai-settings.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.ai-settings.*') ? 'bg-gray-700' : '' }}">AI 智能助手</a>
        <a href="{{ route('admin.approval-flows.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.approval-flows.*') ? 'bg-gray-700' : '' }}">审批流配置</a>
        <a href="{{ route('admin.audit-logs.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.audit-logs.*') ? 'bg-gray-700' : '' }}">操作日志</a>

        <div class="font-semibold text-gray-400 mb-2 mt-6 px-2 text-xs uppercase tracking-wider">切换</div>
        <a href="{{ route('app.dashboard') }}" class="block px-3 py-2 rounded bg-indigo-600 hover:bg-indigo-500 text-center">进入业务系统</a>
    </nav>
</aside>
