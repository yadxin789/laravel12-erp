@extends('layouts.business')
@section('title', '项目管理')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">项目管理</h2>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2">项目名称</th><th class="px-3 py-2">状态</th>
            <th class="px-3 py-2">开始</th><th class="px-3 py-2">结束</th>
            <th class="px-3 py-2">预算</th><th class="px-3 py-2">任务数</th>
        </tr></thead>
        <tbody>
        @foreach($projects as $p)
            <tr class="border-b">
                <td class="px-3 py-2 font-medium">{{ $p->name }}</td>
                <td class="px-3 py-2"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">{{ $p->status }}</span></td>
                <td class="px-3 py-2">{{ $p->start_date ?: '—' }}</td>
                <td class="px-3 py-2">{{ $p->end_date ?: '—' }}</td>
                <td class="px-3 py-2">¥{{ number_format($p->budget, 2) }}</td>
                <td class="px-3 py-2">{{ $p->tasks_count }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $projects->links() }}</div>
</div>
@endsection
