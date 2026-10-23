<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /** 权限分组的中文名 */
    public const GROUPS = [
        'sales' => '销售',
        'purchase' => '采购',
        'production' => '生产',
        'inventory' => '库存',
        'quality' => '质量',
        'finance' => '财务',
        'crm' => '客户关系',
        'masterdata' => '主数据',
        'reports' => '报表',
        'system' => '系统',
        'hr' => '人力资源',
        'projects' => '项目管理',
        'assets' => '固定资产',
        'ecommerce' => '电商',
    ];

    public function index()
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get();

        return view('admin.permissions.index', [
            'permissions' => $permissions,
            'groups' => self::GROUPS,
        ]);
    }

    public function create()
    {
        return view('admin.permissions.create', ['groups' => self::GROUPS]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions',
            'display' => 'nullable|string|max:100',
            'group' => 'nullable|string|max:50',
        ]);

        Permission::create([
            'name' => $validated['name'],
            'display' => $validated['display'] ?: null,
            'group' => $validated['group'] ?: null,
        ]);

        return redirect()->route('admin.permissions.index')->with('success', '权限创建成功');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', [
            'permission' => $permission,
            'groups' => self::GROUPS,
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'display' => 'nullable|string|max:100',
            'group' => 'nullable|string|max:50',
        ]);

        $permission->update([
            'name' => $validated['name'],
            'display' => $validated['display'] ?: null,
            'group' => $validated['group'] ?: null,
        ]);

        return redirect()->route('admin.permissions.index')->with('success', '权限更新成功');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('success', '权限删除成功');
    }
}
