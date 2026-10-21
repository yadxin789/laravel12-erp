@extends('layouts.admin')

@section('title', '工艺路线管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">工艺路线管理</h2>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">编码</th>
                <th class="text-left py-3">物料名称</th>
                <th class="text-left py-3">版本</th>
                <th class="text-left py-3">生效日期</th>
                <th class="text-left py-3">失效日期</th>
                <th class="text-left py-3">工序数</th>
                <th class="text-left py-3">标准工时(小时)</th>
                <th class="text-left py-3">状态</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($routings as $routing)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $routing->code }}</td>
                <td class="py-3">{{ $routing->item->name ?? '-' }}</td>
                <td class="py-3">{{ $routing->version }}</td>
                <td class="py-3">{{ $routing->effective_date }}</td>
                <td class="py-3">{{ $routing->expiry_date ?? '-' }}</td>
                <td class="py-3">{{ $routing->operations->count() }}</td>
                <td class="py-3">{{ $routing->operations->sum('standard_time') }}</td>
                <td class="py-3">{{ $routing->status }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('admin.routings.show', $routing) }}" class="text-blue-600 hover:text-blue-800 mr-2">查看</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $routings->links() }}
</div>
@endsection