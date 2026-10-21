@extends('layouts.business')
@section('title', '完工入库详情')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">完工入库详情 #{{ $receipt->receipt_no }}</h2>
        <a href="{{ route('app.production.receipts.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">返回</a>
    </div>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div><span class="font-semibold">入库单号：</span>{{ $receipt->receipt_no }}</div>
        <div><span class="font-semibold">生产工单：</span>{{ $receipt->productionOrder?->order_no ?? '-' }}</div>
        <div><span class="font-semibold">仓库：</span>{{ $receipt->warehouse?->name ?? '-' }}</div>
        <div><span class="font-semibold">入库数量：</span>{{ $receipt->quantity }}</div>
        <div><span class="font-semibold">批次号：</span>{{ $receipt->batch_number ?? '-' }}</div>
        <div><span class="font-semibold">入库日期：</span>{{ $receipt->receipt_date ?? '-' }}</div>
        <div class="col-span-2"><span class="font-semibold">备注：</span>{{ $receipt->remark ?? '-' }}</div>
    </div>
    @if($receipt->lines->isNotEmpty())
    <h3 class="text-lg font-semibold mb-2">入库明细</h3>
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2 text-left">物料</th>
                <th class="border px-4 py-2 text-right">数量</th>
                <th class="border px-4 py-2 text-left">批次号</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->lines as $line)
            <tr class="hover:bg-gray-50">
                <td class="border px-4 py-2">{{ $line->item?->name }}</td>
                <td class="border px-4 py-2 text-right">{{ $line->quantity }}</td>
                <td class="border px-4 py-2">{{ $line->batch_number ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
