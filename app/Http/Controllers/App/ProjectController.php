<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Customer;
use App\Models\Project\Project;
use App\Models\Project\ProjectTask;
use App\Models\Project\TimeEntry;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * 项目管理模块：项目 → 任务 → 工时
 */
class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('manager')->withCount('tasks');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        $projects = $query->latest()->paginate(20)->appends($request->query());

        $stats = [
            'total' => Project::count(),
            'in_progress' => Project::where('status', 'in_progress')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'budget' => (float) Project::sum('budget'),
        ];

        return view('app.projects.index', [
            'projects' => $projects,
            'stats' => $stats,
            'statusLabels' => Project::STATUS,
        ]);
    }

    public function create()
    {
        return view('app.projects.create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'statusLabels' => Project::STATUS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', array_keys(Project::STATUS)),
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'manager_id' => 'nullable|exists:users,id',
        ], [
            'name.required' => '请填写项目名称',
            'end_date.after_or_equal' => '结束日期不能早于开始日期',
        ]);

        $data['manager_id'] = $data['manager_id'] ?? auth()->id();

        $project = Project::create($data);

        return redirect()->route('app.projects.show', $project)->with('success', '项目已创建');
    }

    public function show(Project $project)
    {
        $project->load(['manager', 'customer', 'tasks.assignee']);

        $timeEntries = TimeEntry::with(['user', 'task'])
            ->whereIn('project_task_id', $project->tasks->pluck('id'))
            ->latest('date')
            ->limit(20)
            ->get();

        return view('app.projects.show', [
            'project' => $project,
            'timeEntries' => $timeEntries,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'statusLabels' => Project::STATUS,
            'taskStatusLabels' => ProjectTask::STATUS,
            'priorityLabels' => ProjectTask::PRIORITY,
        ]);
    }

    public function edit(Project $project)
    {
        return view('app.projects.edit', [
            'project' => $project,
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'statusLabels' => Project::STATUS,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', array_keys(Project::STATUS)),
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'manager_id' => 'nullable|exists:users,id',
        ], [
            'name.required' => '请填写项目名称',
            'end_date.after_or_equal' => '结束日期不能早于开始日期',
        ]);

        $project->update($data);

        return redirect()->route('app.projects.show', $project)->with('success', '项目已更新');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('app.projects.index')->with('success', '项目已删除');
    }

    // ===================== 任务 =====================

    public function storeTask(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', array_keys(ProjectTask::STATUS)),
            'priority' => 'required|in:1,2,3',
            'due_date' => 'nullable|date',
            'assignee_id' => 'nullable|exists:users,id',
        ], ['name.required' => '请填写任务名称']);

        $data['project_id'] = $project->id;

        ProjectTask::create($data);

        return redirect()->route('app.projects.show', $project)->with('success', '任务已添加');
    }

    public function updateTask(Request $request, ProjectTask $task)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(ProjectTask::STATUS)),
            'priority' => 'nullable|in:1,2,3',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $task->update($data);

        return back()->with('success', '任务已更新');
    }

    public function destroyTask(ProjectTask $task)
    {
        $projectId = $task->project_id;
        $task->timeEntries()->delete();
        $task->delete();

        return redirect()->route('app.projects.show', $projectId)->with('success', '任务已删除');
    }

    // ===================== 工时 =====================

    public function storeTime(Request $request, ProjectTask $task)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.1|max:24',
            'description' => 'nullable|string',
        ], [
            'date.required' => '请选择日期',
            'hours.required' => '请填写工时',
            'hours.max' => '单日工时不能超过 24 小时',
        ]);

        $data['project_task_id'] = $task->id;
        $data['user_id'] = auth()->id();

        TimeEntry::create($data);

        return redirect()->route('app.projects.show', $task->project_id)->with('success', '工时已登记');
    }

    public function destroyTime(TimeEntry $entry)
    {
        $projectId = $entry->task?->project_id;
        $entry->delete();

        return redirect()->route('app.projects.show', $projectId)->with('success', '工时记录已删除');
    }
}
