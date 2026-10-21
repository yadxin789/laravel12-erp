@extends('layouts.business')
@section('title', '我的审批待办')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">我的审批待办（{{ $tasks->total() }}）</h2>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2 text-left">单据类型</th>
            <th class="px-3 py-2 text-left">单据ID</th>
            <th class="px-3 py-2 text-left">审批节点</th>
            <th class="px-3 py-2 text-left">提交时间</th>
            <th class="px-3 py-2 text-center">操作</th>
        </tr></thead>
        <tbody>
        @forelse($tasks as $task)
            <tr class="border-b">
                <td class="px-3 py-2">{{ $task->document_type }}</td>
                <td class="px-3 py-2 font-mono">#{{ $task->document_id }}</td>
                <td class="px-3 py-2">{{ $task->step_name }}（第{{ $task->step_order }}步）</td>
                <td class="px-3 py-2 text-gray-500">{{ $task->created_at->format('Y-m-d H:i') }}</td>
                <td class="px-3 py-2 text-center">
                    <form method="POST" action="{{ route('app.approvals.handle', $task) }}" class="inline">
                        @csrf
                        <input type="text" name="comment" placeholder="审批意见" class="border rounded px-2 py-1 text-xs w-32">
                        <button name="action" value="approve" class="bg-green-600 text-white px-2 py-1 rounded text-xs ml-1">通过</button>
                        <button name="action" value="reject" class="bg-red-600 text-white px-2 py-1 rounded text-xs ml-1">驳回</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-3 py-8 text-center text-gray-400">暂无待办审批</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $tasks->links() }}</div>
</div>
@endsection
