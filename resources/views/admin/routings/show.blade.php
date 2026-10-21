@extends('layouts.admin')

@section('title', '工艺路线详情')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">工艺路线详情</h2>
        <a href="{{ route('admin.routings.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">返回列表</a>
    </div>

    <!-- 主表信息 -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="border rounded-lg p-4">
            <div class="text-sm text-gray-500 mb-1">编码</div>
            <div class="font-medium">{{ $routing->code }}</div>
        </div>
        <div class="border rounded-lg p-4">
            <div class="text-sm text-gray-500 mb-1">物料</div>
            <div class="font-medium">{{ $routing->item->name ?? '-' }}</div>
        </div>
        <div class="border rounded-lg p-4">
            <div class="text-sm text-gray-500 mb-1">版本</div>
            <div class="font-medium">{{ $routing->version }}</div>
        </div>
        <div class="border rounded-lg p-4">
            <div class="text-sm text-gray-500 mb-1">状态</div>
            <div class="font-medium">{{ $routing->status }}</div>
        </div>
        <div class="border rounded-lg p-4">
            <div class="text-sm text-gray-500 mb-1">生效日期</div>
            <div class="font-medium">{{ $routing->effective_date }}</div>
        </div>
        <div class="border rounded-lg p-4">
            <div class="text-sm text-gray-500 mb-1">失效日期</div>
            <div class="font-medium">{{ $routing->expiry_date ?? '-' }}</div>
        </div>
        <div class="border rounded-lg p-4 col-span-2">
            <div class="text-sm text-gray-500 mb-1">描述</div>
            <div class="font-medium">{{ $routing->description ?? '-' }}</div>
        </div>
    </div>

    <!-- 子表：工序列表 -->
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">工序列表</h3>
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3">工序号</th>
                    <th class="text-left py-3">工序编码</th>
                    <th class="text-left py-3">工序名称</th>
                    <th class="text-left py-3">工作中心</th>
                    <th class="text-left py-3">标准工时</th>
                    <th class="text-left py-3">准备时间</th>
                </tr>
            </thead>
            <tbody>
                @foreach($routing->operations as $operation)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3">{{ $operation->operation_number }}</td>
                    <td class="py-3">{{ $operation->operation_code }}</td>
                    <td class="py-3">{{ $operation->operation_name }}</td>
                    <td class="py-3">{{ $operation->workCenter->name ?? '-' }}</td>
                    <td class="py-3">{{ $operation->standard_time }}</td>
                    <td class="py-3">{{ $operation->setup_time }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection