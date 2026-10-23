<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Department;
use App\Models\MasterData\Position;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

/**
 * 岗位管理（组织主数据）
 *
 * 岗位描述「编制」（某部门下有什么岗位、编制几人），与系统角色解耦：
 * 岗位用于人员归属、汇报关系与审批路由，角色才决定能做什么。
 */
class PositionController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::with('department');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $positions = $query->orderBy('sort_order')->orderBy('code')->paginate(20);
        $departments = Department::orderBy('sort_order')->orderBy('code')->get();

        return view('admin.positions.index', compact('positions', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('sort_order')->orderBy('code')->get();
        $roles = Role::orderBy('name')->get(['id', 'name', 'display_name']);

        return view('admin.positions.create', compact('departments', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:30|unique:positions,code',
            'name' => 'required|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'level' => 'nullable|string|max:30',
            'headcount' => 'nullable|integer|min:1',
            'default_role' => 'nullable|string|max:50|exists:roles,name',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ], [
            'code.required' => '请填写岗位编码',
            'code.unique' => '该岗位编码已存在',
            'name.required' => '请填写岗位名称',
            'default_role.exists' => '所选角色不存在',
        ]);

        $data['active'] = $request->boolean('active');
        $data['headcount'] = $data['headcount'] ?? 1;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        Position::create($data);

        return redirect()->route('admin.positions.index')->with('success', '岗位创建成功');
    }

    public function edit(Position $position)
    {
        $departments = Department::orderBy('sort_order')->orderBy('code')->get();
        $roles = Role::orderBy('name')->get(['id', 'name', 'display_name']);

        return view('admin.positions.edit', compact('position', 'departments', 'roles'));
    }

    public function update(Request $request, Position $position)
    {
        $data = $request->validate([
            'code' => 'required|string|max:30|unique:positions,code,' . $position->id,
            'name' => 'required|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'level' => 'nullable|string|max:30',
            'headcount' => 'nullable|integer|min:1',
            'default_role' => 'nullable|string|max:50|exists:roles,name',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ], [
            'code.unique' => '该岗位编码已存在',
            'name.required' => '请填写岗位名称',
            'default_role.exists' => '所选角色不存在',
        ]);

        $data['active'] = $request->boolean('active');
        $data['updated_by'] = auth()->id();

        $position->update($data);

        return redirect()->route('admin.positions.index')->with('success', '岗位更新成功');
    }

    public function destroy(Position $position)
    {
        $position->delete();

        return redirect()->route('admin.positions.index')->with('success', '岗位已删除');
    }
}
