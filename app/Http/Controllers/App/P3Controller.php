<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\Attendance;
use App\Models\Project\Project;
use App\Models\Project\ProjectTask;
use App\Models\Project\TimeEntry;
use App\Models\Asset\Asset;
use App\Models\Ecommerce\EcommerceOrder;
use Illuminate\Http\Request;

class P3Controller extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasAnyRole(['admin', 'viewer'])) {
                abort(403, '无访问权限');
            }
            return $next($request);
        });
    }

    // ========== HR ==========
    public function employees()
    {
        $employees = Employee::latest()->paginate(20);
        return view('app.hr.employees', compact('employees'));
    }

    public function storeEmployee(Request $request)
    {
        $data = $request->validate([
            'emp_no' => 'required|unique:employees',
            'name' => 'required',
            'department' => 'nullable',
            'position' => 'nullable',
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'hire_date' => 'nullable|date',
        ]);
        Employee::create($data);
        return back()->with('success', '员工已添加');
    }

    public function attendance()
    {
        $attendances = Attendance::with('employee')->latest('date')->paginate(20);
        return view('app.hr.attendance', compact('attendances'));
    }

    // ========== 项目 ==========
    public function projects()
    {
        $projects = Project::withCount('tasks')->latest()->paginate(20);
        return view('app.projects.index', compact('projects'));
    }

    public function storeProject(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric',
        ]);
        $data['manager_id'] = auth()->id();
        Project::create($data);
        return back()->with('success', '项目已创建');
    }

    // ========== 资产 ==========
    public function assets()
    {
        $assets = Asset::latest()->paginate(20);
        return view('app.assets.index', compact('assets'));
    }

    public function storeAsset(Request $request)
    {
        $data = $request->validate([
            'asset_no' => 'required|unique:assets',
            'name' => 'required',
            'category' => 'nullable',
            'original_cost' => 'required|numeric',
            'useful_life_years' => 'required|integer|min:1',
            'purchase_date' => 'required|date',
        ]);
        $data['start_depreciation_date'] = $data['purchase_date'];
        Asset::create($data);
        return back()->with('success', '资产已登记');
    }

    // ========== 电商 ==========
    public function ecommerce()
    {
        $orders = EcommerceOrder::latest()->paginate(20);
        return view('app.ecommerce.index', compact('orders'));
    }

    public function syncEcommerce(Request $request)
    {
        // 模拟同步（实际对接各平台API）
        return back()->with('success', '电商订单同步完成（模拟）');
    }
}
