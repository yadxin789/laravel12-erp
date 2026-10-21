@extends('layouts.business')

@section('title', '缺陷管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">缺陷管理</h2>
        <span class="text-sm text-gray-500">缺陷代码字典 + 最近缺陷记录</span>
    </div>

    {{-- 统计卡片 --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg">
            <p class="text-blue-600 text-sm">缺陷代码总数</p>
            <p class="text-2xl font-bold text-blue-800">{{ $defectCodes->total() }}</p>
        </div>
        <div class="bg-orange-50 p-4 rounded-lg">
            <p class="text-orange-600 text-sm">最近缺陷记录</p>
            <p class="text-2xl font-bold text-orange-800">{{ $defectRecords->count() }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
            <p class="text-green-600 text-sm">启用中代码</p>
            <p class="text-2xl font-bold text-green-800">{{ $defectCodes->where('active', true)->count() }}</p>
        </div>
    </div>

    {{-- 缺陷代码字典 --}}
    <h3 class="font-medium text-gray-700 mb-3">缺陷代码字典</h3>
    <table class="w-full mb-6">
        <thead>
            <tr class="border-b bg-gray-50">
                <th class="text-left py-2 px-3 text-sm font-medium">代码</th>
                <th class="text-left py-2 px-3 text-sm font-medium">名称</th>
                <th class="text-left py-2 px-3 text-sm font-medium">分类</th>
                <th class="text-left py-2 px-3 text-sm font-medium">描述</th>
                <th class="text-center py-2 px-3 text-sm font-medium">状态</th>
            </tr>
        </thead>
        <tbody>
            @forelse($defectCodes as $code)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-2 px-3 font-mono text-sm">{{ $code->code }}</td>
                <td class="py-2 px-3 text-sm">{{ $code->name }}</td>
                <td class="py-2 px-3 text-sm">
                    <span class="px-2 py-1 rounded text-xs bg-gray-100">{{ $code->category }}</span>
                </td>
                <td class="py-2 px-3 text-sm text-gray-500">{{ $code->description ?? '—' }}</td>
                <td class="py-2 px-3 text-center">
                    @if($code->active)
                        <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">启用</span>
                    @else
                        <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-600">停用</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="py-8 text-center text-gray-400">暂无缺陷代码数据</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {{ $defectCodes->links() }}

    {{-- 最近缺陷记录 --}}
    <h3 class="font-medium text-gray-700 mb-3 mt-6">最近缺陷记录</h3>
    <table class="w-full">
        <thead>
            <tr class="border-b bg-gray-50">
                <th class="text-left py-2 px-3 text-sm font-medium">记录号</th>
                <th class="text-left py-2 px-3 text-sm font-medium">缺陷类型</th>
                <th class="text-left py-2 px-3 text-sm font-medium">缺陷代码</th>
                <th class="text-left py-2 px-3 text-sm font-medium">物料</th>
                <th class="text-right py-2 px-3 text-sm font-medium">数量</th>
                <th class="text-left py-2 px-3 text-sm font-medium">处置方式</th>
            </tr>
        </thead>
        <tbody>
            @forelse($defectRecords as $record)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-2 px-3 font-mono text-sm">{{ $record->record_no ?? '—' }}</td>
                <td class="py-2 px-3 text-sm">{{ $record->defect_type ?? '—' }}</td>
                <td class="py-2 px-3 text-sm">{{ $record->defectCode?->name ?? '—' }}</td>
                <td class="py-2 px-3 text-sm">{{ $record->item?->name ?? '—' }}</td>
                <td class="py-2 px-3 text-right text-sm">{{ $record->quantity }}</td>
                <td class="py-2 px-3 text-sm">{{ $record->disposition ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="py-8 text-center text-gray-400">暂无缺陷记录</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
