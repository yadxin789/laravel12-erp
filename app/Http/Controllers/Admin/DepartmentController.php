<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Department;
use Illuminate\Http\Request;

/**
 * 部门管理（组织主数据）
 *
 * 部门属于「组织架构」维度，只描述组织归属与汇报关系，
 * 不直接参与授权（授权一律由角色承担）。
 */
class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('parent')
            ->withCount('children')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->paginate(20);

        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        $parents = Department::orderBy('sort_order')->orderBy('code')->get();

        return view('admin.departments.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:30|unique:departments,code',
            'name' => 'required|string|max:100',
            'short_name' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:departments,id',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ], [
            'code.required' => '请填写部门编码',
            'code.unique' => '该部门编码已存在',
            'name.required' => '请填写部门名称',
        ]);

        $data['active'] = $request->boolean('active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        Department::create($data);

        return redirect()->route('admin.departments.index')->with('success', '部门创建成功');
    }

    public function edit(Department $department)
    {
        $parents = Department::where('id', '!=', $department->id)
            ->orderBy('sort_order')->orderBy('code')->get();

        return view('admin.departments.edit', compact('department', 'parents'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'code' => 'required|string|max:30|unique:departments,code,' . $department->id,
            'name' => 'required|string|max:100',
            'short_name' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:departments,id',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ], [
            'code.unique' => '该部门编码已存在',
            'name.required' => '请填写部门名称',
        ]);

        // 防止把上级设成自己或自己的下级，形成环
        if (! empty($data['parent_id']) && (int) $data['parent_id'] === $department->id) {
            return back()->withErrors(['parent_id' => '上级部门不能是自己'])->withInput();
        }

        $data['active'] = $request->boolean('active');
        $data['updated_by'] = auth()->id();

        $department->update($data);

        return redirect()->route('admin.departments.index')->with('success', '部门更新成功');
    }

    public function destroy(Department $department)
    {
        if ($department->children()->exists()) {
            return redirect()->route('admin.departments.index')
                ->with('error', '该部门下还有子部门，请先处理子部门');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')->with('success', '部门已删除');
    }
}
