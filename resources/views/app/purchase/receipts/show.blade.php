@extends('layouts.business')
@section('title', '收货单详情')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">收货单详情 #{{ $receipt->receipt_no }}</h2>
        <a href="{{ route('app.purchase.receipts.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">返回</a>
    </div>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div><span class="font-semibold">收货单号：</span>{{ $receipt->receipt_no }}</div>
        <div><span class="font-semibold">采购订单：</span>{{ $receipt->purchaseOrder?->order_no ?? '-' }}</div>
        <div><span class="font-semibold">仓库：</span>{{ $receipt->warehouse?->name ?? '-' }}</div>
        <div><span class="font-semibold">状态：</span>{{ $receipt->status ?? '-' }}</div>
        <div><span class="font-semibold">收货日期：</span>{{ $receipt->receipt_date ?? '-' }}</div>
        <div class="col-span-2"><span class="font-semibold">备注：</span>{{ $receipt->remark ?? '-' }}</div>
    </div>
    @if($receipt->lines->isNotEmpty())
    <h3 class="text-lg font-semibold mb-2">收货明细</h3>
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2 text-left">物料</th>
                <th class="border px-4 py-2 text-right">订单数量</th>
                <th class="border px-4 py-2 text-right">收货数量</th>
                <th class="border px-4 py-2 text-left">批次号</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->lines as $line)
            <tr class="hover:bg-gray-50">
                <td class="border px-4 py-2">{{ $line->item?->name }}</td>
                <td class="border px-4 py-2 text-right">{{ $line->order_quantity ?? '-' }}</td>
                <td class="border px-4 py-2 text-right">{{ $line->quantity }}</td>
                <td class="border px-4 py-2">{{ $line->batch_number ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
