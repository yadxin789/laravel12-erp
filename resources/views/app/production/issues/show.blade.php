@extends('layouts.business')
@section('title', '领料单详情')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">领料单详情 #{{ $issue->issue_no }}</h2>
        <a href="{{ route('app.production.issues.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">返回</a>
    </div>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div><span class="font-semibold">领料单号：</span>{{ $issue->issue_no }}</div>
        <div><span class="font-semibold">生产工单：</span>{{ $issue->productionOrder?->order_no ?? '-' }}</div>
        <div><span class="font-semibold">仓库：</span>{{ $issue->warehouse?->name ?? '-' }}</div>
        <div><span class="font-semibold">状态：</span>{{ $issue->status ?? '-' }}</div>
        <div><span class="font-semibold">领料日期：</span>{{ $issue->issue_date ?? '-' }}</div>
        <div><span class="font-semibold">备注：</span>{{ $issue->remark ?? '-' }}</div>
    </div>
    @if($issue->lines->isNotEmpty())
    <h3 class="text-lg font-semibold mb-2">领料明细</h3>
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2 text-left">物料</th>
                <th class="border px-4 py-2 text-right">需求数量</th>
                <th class="border px-4 py-2 text-right">已领数量</th>
                <th class="border px-4 py-2 text-left">库位</th>
                <th class="border px-4 py-2 text-left">批次号</th>
            </tr>
        </thead>
        <tbody>
            @foreach($issue->lines as $line)
            <tr class="hover:bg-gray-50">
                <td class="border px-4 py-2">{{ $line->item?->name }}</td>
                <td class="border px-4 py-2 text-right">{{ $line->required_quantity }}</td>
                <td class="border px-4 py-2 text-right">{{ $line->issued_quantity }}</td>
                <td class="border px-4 py-2">{{ $line->location?->name ?? '-' }}</td>
                <td class="border px-4 py-2">{{ $line->batch_number ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
