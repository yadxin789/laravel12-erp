@extends('layouts.business')

@section('title', '检验单详情')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">检验单: {{ $inspection->inspection_no }}</h2>
    </div>
    
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div>
            <p class="text-gray-500 text-sm">检验类型</p>
            <p class="font-medium">{{ $inspection->inspection_type }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">检验日期</p>
            <p class="font-medium">{{ $inspection->inspection_date }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">物料</p>
            <p class="font-medium">{{ $inspection->item?->name }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">批次号</p>
            <p class="font-medium">{{ $inspection->batch_number ?? '-' }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">检验数量</p>
            <p class="font-medium">{{ $inspection->inspected_size }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">合格数量</p>
            <p class="font-medium text-green-600">{{ $inspection->qualified_size }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">不合格数量</p>
            <p class="font-medium text-red-600">{{ $inspection->rejected_size }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">结果</p>
            <p class="font-medium {{ $inspection->result === 'qualified' ? 'text-green-600' : 'text-red-600' }}">
                {{ $inspection->result === 'qualified' ? '合格' : '不合格' }}
            </p>
        </div>
    </div>
</div>
@endsection
