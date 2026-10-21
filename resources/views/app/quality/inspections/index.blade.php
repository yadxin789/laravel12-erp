@extends('layouts.business')

@section('title', '检验管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">检验管理</h2>
        <a href="{{ route('app.quality.inspections.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建检验单</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">检验单号</th>
                <th class="text-left py-3">检验类型</th>
                <th class="text-left py-3">物料</th>
                <th class="text-left py-3">批次号</th>
                <th class="text-right py-3">检验数量</th>
                <th class="text-center py-3">结果</th>
                <th class="text-center py-3">状态</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inspections as $inspection)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $inspection->inspection_no }}</td>
                <td class="py-3">{{ $inspection->inspection_type }}</td>
                <td class="py-3">{{ $inspection->item?->name }}</td>
                <td class="py-3">{{ $inspection->batch_number ?? '-' }}</td>
                <td class="py-3 text-right">{{ $inspection->inspected_size }}</td>
                <td class="py-3 text-center">
                    <span class="{{ $inspection->result === 'qualified' ? 'text-green-600' : 'text-red-600' }}">{{ $inspection->result === 'qualified' ? '合格' : '不合格' }}</span>
                </td>
                <td class="py-3 text-center">{{ $inspection->status }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('app.quality.inspections.show', $inspection) }}" class="text-blue-600 hover:text-blue-800">查看</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $inspections->links() }}
</div>
@endsection
