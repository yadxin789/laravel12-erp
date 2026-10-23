@extends('layouts.business')

@section('title', '我的审批待办')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-check"></i>我的审批待办（{{ $tasks->total() }}）</h2>
        <a href="{{ route('app.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-gauge me-1"></i>返回仪表盘
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>单据类型</th>
                            <th>单据ID</th>
                            <th>审批节点</th>
                            <th>提交时间</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td><span class="badge bg-light text-secondary border">{{ $task->document_type }}</span></td>
                                <td class="font-monospace small">#{{ $task->document_id }}</td>
                                <td>{{ $task->step_name }}（第{{ $task->step_order }}步）</td>
                                <td class="text-muted">{{ $task->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('app.approvals.handle', $task) }}" class="d-flex flex-wrap gap-2 justify-content-end">
                                        @csrf
                                        <input type="text" name="comment" placeholder="审批意见" class="form-control form-control-sm" style="max-width: 14rem;">
                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">
                                            <i class="fa-solid fa-check me-1"></i>通过
                                        </button>
                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">
                                            <i class="fa-solid fa-xmark me-1"></i>驳回
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="erp-empty">暂无待办审批</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $tasks->links() }}
            </div>
        </div>
    </div>
@endsection
