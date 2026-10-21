@extends('layouts.business')
@section('title', '质量报告')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6">质量报告</h2>
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-100 p-4 rounded">
            <div class="text-lg font-bold">{{ $totalInspections ?? 0 }}</div>
            <div class="text-sm text-gray-600">总检验数</div>
        </div>
        <div class="bg-green-100 p-4 rounded">
            <div class="text-lg font-bold text-green-700">{{ $passCount ?? 0 }}</div>
            <div class="text-sm text-gray-600">合格</div>
        </div>
        <div class="bg-red-100 p-4 rounded">
            <div class="text-lg font-bold text-red-700">{{ $failCount ?? 0 }}</div>
            <div class="text-sm text-gray-600">不合格</div>
        </div>
    </div>
    @if(isset($inspections) && $inspections->isNotEmpty())
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2 text-left">检验单号</th>
                <th class="border px-4 py-2 text-left">检验类型</th>
                <th class="border px-4 py-2 text-left">物料</th>
                <th class="border px-4 py-2 text-right">检验数量</th>
                <th class="border px-4 py-2 text-right">合格</th>
                <th class="border px-4 py-2 text-right">不合格</th>
                <th class="border px-4 py-2 text-left">结果</th>
                <th class="border px-4 py-2 text-left">日期</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inspections as $inspection)
            <tr class="hover:bg-gray-50">
                <td class="border px-4 py-2">{{ $inspection->inspection_no }}</td>
                <td class="border px-4 py-2">{{ $inspection->inspection_type }}</td>
                <td class="border px-4 py-2">{{ $inspection->item?->name }}</td>
                <td class="border px-4 py-2 text-right">{{ $inspection->inspected_size }}</td>
                <td class="border px-4 py-2 text-right text-green-600">{{ $inspection->qualified_size }}</td>
                <td class="border px-4 py-2 text-right text-red-600">{{ $inspection->rejected_size }}</td>
                <td class="border px-4 py-2">{{ $inspection->result }}</td>
                <td class="border px-4 py-2">{{ $inspection->inspection_date ?? $inspection->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $inspections->links() }}</div>
    @else
    <div class="text-gray-500 text-center py-8">暂无质量数据</div>
    @endif
</div>
@endsection
