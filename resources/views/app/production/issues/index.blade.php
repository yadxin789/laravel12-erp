@extends('layouts.business')

@section('title', '领料管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">领料管理</h2>
        <a href="{{ route('app.production.issues.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建领料单</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">领料单号</th>
                <th class="text-left py-3">生产工单</th>
                <th class="text-left py-3">仓库</th>
                <th class="text-left py-3">领料日期</th>
                <th class="text-center py-3">状态</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($issues as $issue)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $issue->issue_no }}</td>
                <td class="py-3">{{ $issue->productionOrder?->order_no }}</td>
                <td class="py-3">{{ $issue->warehouse?->name }}</td>
                <td class="py-3">{{ $issue->issue_date }}</td>
                <td class="py-3 text-center">{{ $issue->status }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('app.production.issues.show', $issue) }}" class="text-blue-600 hover:text-blue-800">查看</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $issues->links() }}
</div>
@endsection
