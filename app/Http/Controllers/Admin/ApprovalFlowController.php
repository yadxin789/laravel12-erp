<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\ApprovalFlow;
use App\Models\System\ApprovalFlowStep;
use App\Models\System\ApprovalTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalFlowController extends Controller
{
    /**
     * 审批流列表
     */
    public function index()
    {
        $flows = ApprovalFlow::with('steps')->get();
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get(['id', 'name', 'display_name']);

        return view('admin.approval-flows.index', compact('flows', 'roles'));
    }

    /**
     * 创建审批流
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'document_type' => 'required|string|max:50',
            'steps' => 'required|array|min:1',
            'steps.*.name' => 'required|string',
            'steps.*.approver_type' => 'required|in:role,user',
            'steps.*.approver_value' => 'required|string',
        ]);

        DB::transaction(function () use ($validated) {
            $flow = ApprovalFlow::create([
                'name' => $validated['name'],
                'document_type' => $validated['document_type'],
                'enabled' => true,
            ]);

            foreach ($validated['steps'] as $i => $step) {
                ApprovalFlowStep::create([
                    'approval_flow_id' => $flow->id,
                    'step_order' => $i + 1,
                    'name' => $step['name'],
                    'approver_type' => $step['approver_type'],
                    'approver_value' => $step['approver_value'],
                ]);
            }
        });

        return back()->with('success', '审批流已创建');
    }

    /**
     * 我的待办审批
     */
    public function myTasks()
    {
        $tasks = ApprovalTask::with('approver')
            ->where('approver_id', auth()->id())
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('app.approvals.my-tasks', compact('tasks'));
    }

    /**
     * 处理审批
     */
    public function handleTask(Request $request, ApprovalTask $task)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'comment' => 'nullable|string',
        ]);

        $task->update([
            'status' => $request->action === 'approve' ? 'approved' : 'rejected',
            'comment' => $request->comment,
            'approved_at' => now(),
        ]);

        // 如果是最后一步且通过，更新单据状态
        // （此处简化，实际可触发事件通知）

        return back()->with('success', '审批已' . ($request->action === 'approve' ? '通过' : '驳回'));
    }
}
