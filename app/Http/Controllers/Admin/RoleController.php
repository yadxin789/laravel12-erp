<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\RoleCatalog;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->paginate(20);

        return view('admin.roles.index', [
            'roles' => $roles,
            'catalog' => RoleCatalog::CATALOG,
        ]);
    }

    public function create()
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get();

        return view('admin.roles.create', [
            'permissions' => $permissions,
            'catalog' => RoleCatalog::CATALOG,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'nullable|string|max:100',
            'permissions' => 'array',
        ], [
            'name.required' => '请填写角色标识',
            'name.unique' => '该角色标识已存在',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'] ?: null,
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', '角色创建成功');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get();

        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
            'catalog' => RoleCatalog::CATALOG,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'nullable|string|max:100',
            'permissions' => 'array',
        ], [
            'name.unique' => '该角色标识已存在',
        ]);

        $role->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'] ?: null,
        ]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')->with('success', '角色更新成功');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', '角色删除成功');
    }
}
