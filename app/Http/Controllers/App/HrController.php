<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\HR\Attendance;
use App\Models\HR\Employee;
use App\Models\HR\JobApplication;
use App\Models\HR\PerformanceReview;
use App\Models\HR\RecruitmentRequest;
use App\Models\HR\SalaryRecord;
use App\Models\MasterData\Department;
use App\Models\MasterData\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 人力资源模块
 *
 * 部门 / 岗位是组织主数据（后台维护），员工档案与考勤是本模块的业务数据。
 */
class HrController extends Controller
{
    public const ATTENDANCE_STATUS = [
        'normal' => '正常',
        'late' => '迟到',
        'early' => '早退',
        'absent' => '缺勤',
        'leave' => '请假',
    ];

    // ===================== 员工档案 =====================

    public function employees(Request $request)
    {
        $query = Employee::with('positionRef');

        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $query->where(function ($q) use ($kw) {
                $q->where('emp_no', 'like', "%{$kw}%")
                    ->orWhere('name', 'like', "%{$kw}%")
                    ->orWhere('phone', 'like', "%{$kw}%");
            });
        }
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->orderBy('emp_no')->paginate(20)->appends($request->query());
        $departments = Department::orderBy('sort_order')->orderBy('code')->pluck('name');

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('status', 'active')->count(),
            'left' => Employee::where('status', 'left')->count(),
        ];

        return view('app.hr.employees.index', compact('employees', 'departments', 'stats'));
    }

    public function create()
    {
        $departments = Department::orderBy('sort_order')->orderBy('code')->get();
        $positions = Position::orderBy('sort_order')->orderBy('code')->get();
        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);

        return view('app.hr.employees.create', compact('departments', 'positions', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'emp_no' => 'required|string|max:50|unique:employees,emp_no',
            'name' => 'required|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'hire_date' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
        ], [
            'emp_no.required' => '请填写工号',
            'emp_no.unique' => '该工号已存在',
            'name.required' => '请填写姓名',
        ]);

        $data['status'] = 'active';
        $data = $this->fillOrgNames($data);

        $employee = Employee::create($data);
        $this->syncRoleByPosition($employee);

        return redirect()->route('app.hr.employees.index')
            ->with('success', '员工档案已创建' . $this->roleSyncTip($employee));
    }

    /**
     * 按「任职岗位 → 默认角色」批量同步全部在职员工的系统角色。
     */
    public function syncRoles()
    {
        $count = 0;

        Employee::where('status', 'active')
            ->whereNotNull('user_id')
            ->with(['positionRef', 'user'])
            ->get()
            ->each(function (Employee $employee) use (&$count) {
                if ($this->syncRoleByPosition($employee)) {
                    $count++;
                }
            });

        return back()->with('success', $count > 0
            ? "已按任职岗位同步 {$count} 名员工的系统角色"
            : '没有需要同步的员工（岗位未设默认角色或未关联系统账号）');
    }

    public function show(Employee $employee)
    {
        $employee->load(['attendances' => fn ($q) => $q->latest('date')->limit(20), 'positionRef', 'departmentRef']);

        $month = now()->format('Y-m');
        $summary = $this->monthSummary($employee, $month);

        return view('app.hr.employees.show', compact('employee', 'summary', 'month'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::orderBy('sort_order')->orderBy('code')->get();
        $positions = Position::orderBy('sort_order')->orderBy('code')->get();
        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);

        return view('app.hr.employees.edit', compact('employee', 'departments', 'positions', 'users'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'emp_no' => 'required|string|max:50|unique:employees,emp_no,' . $employee->id,
            'name' => 'required|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'hire_date' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
        ], [
            'emp_no.unique' => '该工号已存在',
            'name.required' => '请填写姓名',
        ]);

        $data = $this->fillOrgNames($data);
        $employee->update($data);
        $this->syncRoleByPosition($employee);

        return redirect()->route('app.hr.employees.index')
            ->with('success', '员工档案已更新' . $this->roleSyncTip($employee));
    }

    /**
     * 办理离职
     */
    public function leave(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'leave_date' => 'required|date',
        ], ['leave_date.required' => '请选择离职日期']);

        $employee->update([
            'status' => 'left',
            'leave_date' => $data['leave_date'],
        ]);

        return redirect()->route('app.hr.employees.index')
            ->with('success', "员工「{$employee->name}」已办理离职");
    }

    public function destroy(Employee $employee)
    {
        $employee->attendances()->delete();
        $employee->delete();

        return redirect()->route('app.hr.employees.index')->with('success', '员工档案已删除');
    }

    // ===================== 考勤管理 =====================

    public function attendance(Request $request)
    {
        $query = Attendance::with('employee');

        if ($request->filled('month')) {
            $query->whereBetween('date', [
                $request->month . '-01',
                \Carbon\Carbon::parse($request->month . '-01')->endOfMonth()->toDateString(),
            ]);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->latest('date')->paginate(20)->appends($request->query());
        $employees = Employee::where('status', 'active')->orderBy('emp_no')->get(['id', 'emp_no', 'name']);

        $month = $request->month ?: now()->format('Y-m');
        $summary = Attendance::whereBetween('date', [
            $month . '-01',
            \Carbon\Carbon::parse($month . '-01')->endOfMonth()->toDateString(),
        ])->selectRaw('status, count(*) as cnt')->groupBy('status')->pluck('cnt', 'status')->all();

        return view('app.hr.attendance.index', [
            'attendances' => $attendances,
            'employees' => $employees,
            'summary' => $summary,
            'month' => $month,
            'statusLabels' => self::ATTENDANCE_STATUS,
        ]);
    }

    public function attendanceCreate()
    {
        $employees = Employee::where('status', 'active')->orderBy('emp_no')->get(['id', 'emp_no', 'name']);

        return view('app.hr.attendance.create', [
            'employees' => $employees,
            'statusLabels' => self::ATTENDANCE_STATUS,
        ]);
    }

    public function attendanceStore(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable',
            'check_out' => 'nullable',
            'status' => 'required|in:' . implode(',', array_keys(self::ATTENDANCE_STATUS)),
            'remark' => 'nullable|string',
        ], [
            'employee_id.required' => '请选择员工',
            'date.required' => '请选择日期',
        ]);

        $data['check_in'] = $this->toTimestamp($data['check_in'] ?? null, $data['date']);
        $data['check_out'] = $this->toTimestamp($data['check_out'] ?? null, $data['date']);

        // 同一员工同一天只保留一条考勤
        Attendance::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'date' => $data['date']],
            $data
        );

        return redirect()->route('app.hr.attendances.index')->with('success', '考勤记录已保存');
    }

    public function attendanceEdit(Attendance $attendance)
    {
        $employees = Employee::orderBy('emp_no')->get(['id', 'emp_no', 'name']);
        $attendance->check_in_value = $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '';
        $attendance->check_out_value = $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '';

        return view('app.hr.attendance.edit', [
            'attendance' => $attendance,
            'employees' => $employees,
            'statusLabels' => self::ATTENDANCE_STATUS,
        ]);
    }

    public function attendanceUpdate(Request $request, Attendance $attendance)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable',
            'check_out' => 'nullable',
            'status' => 'required|in:' . implode(',', array_keys(self::ATTENDANCE_STATUS)),
            'remark' => 'nullable|string',
        ]);

        $data['check_in'] = $this->toTimestamp($data['check_in'] ?? null, $data['date']);
        $data['check_out'] = $this->toTimestamp($data['check_out'] ?? null, $data['date']);

        $attendance->update($data);

        return redirect()->route('app.hr.attendances.index')->with('success', '考勤记录已更新');
    }

    public function attendanceDestroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()->route('app.hr.attendances.index')->with('success', '考勤记录已删除');
    }

    // ===================== 薪资管理 =====================

    public function payroll(Request $request)
    {
        $query = SalaryRecord::with('employee');

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        } else {
            $query->where('period', now()->format('Y-m'));
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->orderByDesc('period')->orderBy('employee_id')->paginate(20)->appends($request->query());
        $employees = Employee::orderBy('emp_no')->get(['id', 'emp_no', 'name']);

        $period = $request->period ?: now()->format('Y-m');
        $monthRows = SalaryRecord::where('period', $period)->get();
        $stats = [
            'count' => $monthRows->count(),
            'gross' => $monthRows->sum(fn ($r) => $r->grossPay()),
            'deduction' => $monthRows->sum(fn ($r) => $r->totalDeduction()),
            'net' => $monthRows->sum(fn ($r) => $r->calcNetPay()),
            'paid' => $monthRows->where('status', 'paid')->count(),
        ];

        return view('app.hr.payroll.index', [
            'records' => $records,
            'employees' => $employees,
            'stats' => $stats,
            'period' => $period,
            'statusLabels' => SalaryRecord::STATUS,
        ]);
    }

    public function payrollCreate()
    {
        $employees = Employee::orderBy('emp_no')->get(['id', 'emp_no', 'name']);

        return view('app.hr.payroll.create', [
            'employees' => $employees,
            'statusLabels' => SalaryRecord::STATUS,
        ]);
    }

    public function payrollStore(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|regex:/^\d{4}-\d{2}$/',
            'base_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'social_insurance' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'other_deduction' => 'nullable|numeric|min:0',
            'net_pay' => 'nullable|numeric',
            'status' => 'required|in:' . implode(',', array_keys(SalaryRecord::STATUS)),
            'paid_at' => 'nullable|date',
            'remark' => 'nullable|string',
        ], [
            'employee_id.required' => '请选择员工',
            'period.required' => '请填写薪资月份',
            'base_salary.required' => '请填写基本工资',
        ]);

        $exists = SalaryRecord::where('employee_id', $data['employee_id'])
            ->where('period', $data['period'])
            ->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['period' => '该员工此月份工资单已存在']);
        }

        $data = $this->fillSalary($data);
        $data['created_by'] = auth()->id();

        SalaryRecord::create($data);

        return redirect()->route('app.hr.payroll.index', ['period' => $data['period']])
            ->with('success', '工资单已创建');
    }

    public function payrollEdit(SalaryRecord $salary)
    {
        $employees = Employee::orderBy('emp_no')->get(['id', 'emp_no', 'name']);

        return view('app.hr.payroll.edit', [
            'salary' => $salary,
            'employees' => $employees,
            'statusLabels' => SalaryRecord::STATUS,
        ]);
    }

    public function payrollUpdate(Request $request, SalaryRecord $salary)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|regex:/^\d{4}-\d{2}$/',
            'base_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'social_insurance' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'other_deduction' => 'nullable|numeric|min:0',
            'net_pay' => 'nullable|numeric',
            'status' => 'required|in:' . implode(',', array_keys(SalaryRecord::STATUS)),
            'paid_at' => 'nullable|date',
            'remark' => 'nullable|string',
        ]);

        $data = $this->fillSalary($data);
        $salary->update($data);

        return redirect()->route('app.hr.payroll.index', ['period' => $data['period']])
            ->with('success', '工资单已更新');
    }

    /**
     * 工资单状态流转：核准 / 发放 / 退回草稿
     */
    public function payrollStatus(Request $request, SalaryRecord $salary)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(SalaryRecord::STATUS)),
        ]);

        $salary->update([
            'status' => $data['status'],
            'paid_at' => $data['status'] === 'paid' ? ($salary->paid_at ?: now()->toDateString()) : null,
        ]);

        return back()->with('success', '工资单状态已更新为「' . SalaryRecord::STATUS[$data['status']] . '」');
    }

    public function payrollDestroy(SalaryRecord $salary)
    {
        $salary->delete();

        return redirect()->route('app.hr.payroll.index')->with('success', '工资单已删除');
    }

    // ===================== 绩效考核 =====================

    public function performance(Request $request)
    {
        $query = PerformanceReview::with('employee');

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        } else {
            $query->where('period', now()->format('Y-m'));
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('grade')) {
            $query->where('grade', $request->grade);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reviews = $query->orderByDesc('score')->paginate(20)->appends($request->query());
        $employees = Employee::orderBy('emp_no')->get(['id', 'emp_no', 'name']);

        $period = $request->period ?: now()->format('Y-m');
        $rows = PerformanceReview::where('period', $period)->get();
        $stats = [
            'count' => $rows->count(),
            'avg' => $rows->count() ? round($rows->avg('score'), 1) : 0,
            'grades' => $rows->countBy('grade')->all(),
        ];

        return view('app.hr.performance.index', [
            'reviews' => $reviews,
            'employees' => $employees,
            'stats' => $stats,
            'period' => $period,
            'statusLabels' => PerformanceReview::STATUS,
            'gradeLabels' => PerformanceReview::GRADES,
            'typeLabels' => PerformanceReview::TYPES,
        ]);
    }

    public function performanceCreate()
    {
        $employees = Employee::orderBy('emp_no')->get(['id', 'emp_no', 'name']);

        return view('app.hr.performance.create', [
            'employees' => $employees,
            'statusLabels' => PerformanceReview::STATUS,
            'typeLabels' => PerformanceReview::TYPES,
        ]);
    }

    public function performanceStore(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|regex:/^\d{4}-\d{2}$/',
            'type' => 'required|in:' . implode(',', array_keys(PerformanceReview::TYPES)),
            'score' => 'required|numeric|min:0|max:100',
            'reviewer' => 'nullable|string|max:50',
            'summary' => 'nullable|string',
            'improvement' => 'nullable|string',
            'status' => 'required|in:' . implode(',', array_keys(PerformanceReview::STATUS)),
            'reviewed_at' => 'nullable|date',
        ], [
            'employee_id.required' => '请选择员工',
            'score.required' => '请填写考核得分',
            'score.max' => '得分不能超过 100',
        ]);

        $data['grade'] = PerformanceReview::gradeOf((float) $data['score']);
        $data['created_by'] = auth()->id();

        PerformanceReview::create($data);

        return redirect()->route('app.hr.performances.index', ['period' => $data['period']])
            ->with('success', '绩效评估已创建');
    }

    public function performanceEdit(PerformanceReview $review)
    {
        $employees = Employee::orderBy('emp_no')->get(['id', 'emp_no', 'name']);

        return view('app.hr.performance.edit', [
            'review' => $review,
            'employees' => $employees,
            'statusLabels' => PerformanceReview::STATUS,
            'typeLabels' => PerformanceReview::TYPES,
        ]);
    }

    public function performanceUpdate(Request $request, PerformanceReview $review)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|regex:/^\d{4}-\d{2}$/',
            'type' => 'required|in:' . implode(',', array_keys(PerformanceReview::TYPES)),
            'score' => 'required|numeric|min:0|max:100',
            'reviewer' => 'nullable|string|max:50',
            'summary' => 'nullable|string',
            'improvement' => 'nullable|string',
            'status' => 'required|in:' . implode(',', array_keys(PerformanceReview::STATUS)),
            'reviewed_at' => 'nullable|date',
        ], ['score.max' => '得分不能超过 100']);

        $data['grade'] = PerformanceReview::gradeOf((float) $data['score']);
        $review->update($data);

        return redirect()->route('app.hr.performances.index', ['period' => $data['period']])
            ->with('success', '绩效评估已更新');
    }

    public function performanceDestroy(PerformanceReview $review)
    {
        $review->delete();

        return redirect()->route('app.hr.performances.index')->with('success', '绩效评估已删除');
    }

    // ===================== 招聘管理 =====================

    public function recruitment(Request $request)
    {
        $query = RecruitmentRequest::with(['position', 'department'])->withCount('applications');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $requests = $query->latest()->paginate(20)->appends($request->query());
        $departments = Department::orderBy('sort_order')->orderBy('code')->get(['id', 'name']);

        $stats = [
            'open' => RecruitmentRequest::where('status', 'open')->count(),
            'headcount' => (int) RecruitmentRequest::where('status', 'open')->sum('headcount'),
            'hired' => JobApplication::where('status', 'hired')->count(),
            'interview' => JobApplication::where('status', 'interview')->count(),
        ];

        return view('app.hr.recruitment.index', [
            'requests' => $requests,
            'departments' => $departments,
            'stats' => $stats,
            'statusLabels' => RecruitmentRequest::STATUS,
        ]);
    }

    public function recruitmentCreate()
    {
        return view('app.hr.recruitment.create', [
            'positions' => Position::orderBy('sort_order')->orderBy('code')->get(['id', 'name']),
            'departments' => Department::orderBy('sort_order')->orderBy('code')->get(['id', 'name']),
            'statusLabels' => RecruitmentRequest::STATUS,
            'reasons' => RecruitmentRequest::REASONS,
            'employmentTypes' => RecruitmentRequest::EMPLOYMENT_TYPES,
        ]);
    }

    public function recruitmentStore(Request $request)
    {
        $data = $request->validate([
            'req_no' => 'nullable|string|max:30|unique:hr_recruitments,req_no',
            'position_id' => 'nullable|exists:positions,id',
            'department_id' => 'nullable|exists:departments,id',
            'title' => 'required|string|max:200',
            'headcount' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:50',
            'employment_type' => 'required|in:' . implode(',', array_keys(RecruitmentRequest::EMPLOYMENT_TYPES)),
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0',
            'expect_date' => 'nullable|date',
            'status' => 'required|in:' . implode(',', array_keys(RecruitmentRequest::STATUS)),
            'remark' => 'nullable|string',
        ], [
            'title.required' => '请填写需求名称',
            'headcount.required' => '请填写需求人数',
        ]);

        $data['req_no'] = ! empty($data['req_no']) ? $data['req_no'] : $this->nextNo('hr_recruitments', 'req_no', 'REC');
        $data['created_by'] = auth()->id();

        RecruitmentRequest::create($data);

        return redirect()->route('app.hr.recruitments.index')->with('success', '招聘需求已创建');
    }

    public function recruitmentEdit(RecruitmentRequest $recruitment)
    {
        return view('app.hr.recruitment.edit', [
            'recruitment' => $recruitment,
            'positions' => Position::orderBy('sort_order')->orderBy('code')->get(['id', 'name']),
            'departments' => Department::orderBy('sort_order')->orderBy('code')->get(['id', 'name']),
            'statusLabels' => RecruitmentRequest::STATUS,
            'reasons' => RecruitmentRequest::REASONS,
            'employmentTypes' => RecruitmentRequest::EMPLOYMENT_TYPES,
        ]);
    }

    public function recruitmentUpdate(Request $request, RecruitmentRequest $recruitment)
    {
        $data = $request->validate([
            'req_no' => 'nullable|string|max:30|unique:hr_recruitments,req_no,' . $recruitment->id,
            'position_id' => 'nullable|exists:positions,id',
            'department_id' => 'nullable|exists:departments,id',
            'title' => 'required|string|max:200',
            'headcount' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:50',
            'employment_type' => 'required|in:' . implode(',', array_keys(RecruitmentRequest::EMPLOYMENT_TYPES)),
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0',
            'expect_date' => 'nullable|date',
            'status' => 'required|in:' . implode(',', array_keys(RecruitmentRequest::STATUS)),
            'remark' => 'nullable|string',
        ]);

        $recruitment->update($data);

        return redirect()->route('app.hr.recruitments.index')->with('success', '招聘需求已更新');
    }

    public function recruitmentDestroy(RecruitmentRequest $recruitment)
    {
        $recruitment->delete();

        return redirect()->route('app.hr.recruitments.index')->with('success', '招聘需求已删除');
    }

    // ===================== 应聘者管理 =====================

    public function applications(Request $request)
    {
        $query = JobApplication::with(['position', 'recruitment']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }
        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $query->where(function ($q) use ($kw) {
                $q->where('app_no', 'like', "%{$kw}%")
                    ->orWhere('name', 'like', "%{$kw}%")
                    ->orWhere('phone', 'like', "%{$kw}%");
            });
        }

        $applications = $query->latest()->paginate(20)->appends($request->query());

        $all = JobApplication::get();
        $stats = [
            'total' => $all->count(),
            'by_status' => $all->countBy('status')->all(),
            'hired' => $all->where('status', 'hired')->count(),
        ];

        return view('app.hr.applications.index', [
            'applications' => $applications,
            'stats' => $stats,
            'positions' => Position::orderBy('sort_order')->orderBy('code')->get(['id', 'name']),
            'statusLabels' => JobApplication::STATUS,
        ]);
    }

    public function applicationCreate()
    {
        return view('app.hr.applications.create', [
            'positions' => Position::orderBy('sort_order')->orderBy('code')->get(['id', 'name']),
            'recruitments' => RecruitmentRequest::where('status', 'open')->get(['id', 'req_no', 'title']),
            'statusLabels' => JobApplication::STATUS,
            'sources' => JobApplication::SOURCES,
            'educations' => JobApplication::EDUCATIONS,
        ]);
    }

    public function applicationStore(Request $request)
    {
        $data = $request->validate([
            'app_no' => 'nullable|string|max:30|unique:hr_applications,app_no',
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email',
            'position_id' => 'nullable|exists:positions,id',
            'recruitment_id' => 'nullable|exists:hr_recruitments,id',
            'education' => 'nullable|string|max:20',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'expect_salary' => 'nullable|numeric|min:0',
            'source' => 'nullable|string|max:30',
            'status' => 'required|in:' . implode(',', array_keys(JobApplication::STATUS)),
            'interview_at' => 'nullable|date',
            'evaluation' => 'nullable|string',
        ], ['name.required' => '请填写应聘者姓名']);

        $data['app_no'] = ! empty($data['app_no']) ? $data['app_no'] : $this->nextNo('hr_applications', 'app_no', 'APP');
        $data['created_by'] = auth()->id();

        JobApplication::create($data);

        return redirect()->route('app.hr.applications.index')->with('success', '应聘者已登记');
    }

    public function applicationEdit(JobApplication $application)
    {
        return view('app.hr.applications.edit', [
            'application' => $application,
            'positions' => Position::orderBy('sort_order')->orderBy('code')->get(['id', 'name']),
            'recruitments' => RecruitmentRequest::get(['id', 'req_no', 'title']),
            'statusLabels' => JobApplication::STATUS,
            'sources' => JobApplication::SOURCES,
            'educations' => JobApplication::EDUCATIONS,
        ]);
    }

    public function applicationUpdate(Request $request, JobApplication $application)
    {
        $data = $request->validate([
            'app_no' => 'nullable|string|max:30|unique:hr_applications,app_no,' . $application->id,
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email',
            'position_id' => 'nullable|exists:positions,id',
            'recruitment_id' => 'nullable|exists:hr_recruitments,id',
            'education' => 'nullable|string|max:20',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'expect_salary' => 'nullable|numeric|min:0',
            'source' => 'nullable|string|max:30',
            'status' => 'required|in:' . implode(',', array_keys(JobApplication::STATUS)),
            'interview_at' => 'nullable|date',
            'evaluation' => 'nullable|string',
        ]);

        $data['hired_at'] = $data['status'] === 'hired'
            ? ($application->hired_at ?: now()->toDateString())
            : null;

        $application->update($data);

        return redirect()->route('app.hr.applications.index')->with('success', '应聘者信息已更新');
    }

    /**
     * 推进应聘流程：投递 → 筛选 → 面试 → Offer → 录用 / 淘汰
     */
    public function applicationStatus(Request $request, JobApplication $application)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(JobApplication::STATUS)),
        ]);

        $application->update([
            'status' => $data['status'],
            'hired_at' => $data['status'] === 'hired' ? now()->toDateString() : null,
        ]);

        return back()->with('success', '应聘状态已更新为「' . JobApplication::STATUS[$data['status']] . '」');
    }

    /**
     * 录用后转为正式员工档案（招聘 → 在职员工的闭环）
     */
    public function applicationToEmployee(JobApplication $application)
    {
        if ($application->status !== 'hired') {
            return back()->withErrors(['status' => '只有「已录用」的应聘者才能转为员工档案']);
        }

        $employee = Employee::create([
            'emp_no' => $this->nextNo('employees', 'emp_no', 'E'),
            'name' => $application->name,
            'phone' => $application->phone,
            'email' => $application->email,
            'department_id' => $application->recruitment?->department_id,
            'position_id' => $application->position_id,
            'department' => $application->recruitment?->department?->name,
            'position' => $application->position?->name,
            'hire_date' => $application->hired_at ?: now()->toDateString(),
            'status' => 'active',
        ]);

        return redirect()->route('app.hr.employees.show', $employee)
            ->with('success', "应聘者「{$application->name}」已转为员工档案 {$employee->emp_no}");
    }

    public function applicationDestroy(JobApplication $application)
    {
        $application->delete();

        return redirect()->route('app.hr.applications.index')->with('success', '应聘者记录已删除');
    }

    // ===================== 内部方法 =====================

    /**
     * 工资单：补齐空字段并自动计算实发工资（应发 - 扣款）。
     */
    private function fillSalary(array $data): array
    {
        foreach (['allowance', 'bonus', 'overtime_pay', 'social_insurance', 'tax', 'other_deduction'] as $field) {
            $data[$field] = $data[$field] ?? 0;
        }

        $empty = ! isset($data['net_pay']) || $data['net_pay'] === null || $data['net_pay'] === '';
        if ($empty) {
            $gross = $data['base_salary'] + $data['allowance'] + $data['bonus'] + $data['overtime_pay'];
            $deduction = $data['social_insurance'] + $data['tax'] + $data['other_deduction'];
            $data['net_pay'] = round($gross - $deduction, 2);
        }

        return $data;
    }

    /**
     * 生成下一个业务编号（如 E008 / REC0001 / APP0001）。
     */
    private function nextNo(string $table, string $column, string $prefix): string
    {
        $max = DB::table($table)->where($column, 'like', $prefix . '%')->max($column);
        $num = $max ? ((int) preg_replace('/\D/', '', (string) $max)) + 1 : 1;

        return $prefix . str_pad((string) $num, $prefix === 'E' ? 3 : 4, '0', STR_PAD_LEFT);
    }

    /**
     * 由组织主数据回填员工的部门/岗位名称（保留字符串字段，兼容既有统计与展示）。
     */
    private function fillOrgNames(array $data): array
    {
        $department = ! empty($data['department_id'])
            ? Department::find($data['department_id'])
            : null;
        $position = ! empty($data['position_id'])
            ? Position::find($data['position_id'])
            : null;

        $data['department'] = $department?->name;
        $data['position'] = $position?->name;

        return $data;
    }

    /**
     * 任职岗位 → 自动授予角色。
     *
     * 岗位只描述编制，授权由角色完成：
     * 员工关联了系统账号、且其岗位设置了 default_role 时，把该角色同步给账号。
     * 管理员账号（is_admin）不被覆盖，避免误改后台权限。
     *
     * @return bool 是否实际同步了角色
     */
    private function syncRoleByPosition(Employee $employee): bool
    {
        $employee->loadMissing(['positionRef', 'user']);

        $user = $employee->user;
        $role = $employee->positionRef?->default_role;

        if (! $user || ! $role) {
            return false;
        }

        if ($user->is_admin) {
            return false;
        }

        if (! \Spatie\Permission\Models\Role::where('name', $role)->exists()) {
            return false;
        }

        if ($user->hasRole($role) && $user->roles->count() === 1) {
            return false;
        }

        $user->syncRoles([$role]);

        return true;
    }

    /**
     * 保存员工后的补充提示语。
     */
    private function roleSyncTip(Employee $employee): string
    {
        $role = $employee->positionRef?->default_role;

        if (! $role || ! $employee->user_id) {
            return '';
        }

        return '，已按岗位授予角色「' . \App\Support\RoleCatalog::label($role) . '」';
    }

    /**
     * 把 "09:12" 转成完整时间戳；为空返回 null。
     */
    private function toTimestamp(?string $time, string $date): ?string
    {
        if (empty($time)) {
            return null;
        }

        // 若传入的是完整时间字符串（含日期），直接返回
        if (str_contains($time, '-')) {
            return $time;
        }

        return $date . ' ' . $time . ':00';
    }

    private function monthSummary(Employee $employee, string $month): array
    {
        $rows = $employee->attendances()
            ->whereBetween('date', [
                $month . '-01',
                \Carbon\Carbon::parse($month . '-01')->endOfMonth()->toDateString(),
            ])
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->all();

        return $rows;
    }
}
