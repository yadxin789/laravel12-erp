@extends('layouts.business')
@section('title', '采购退货详情')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">采购退货详情 #{{ $return->return_no }}</h2>
        <a href="{{ route('app.purchase.returns.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">返回</a>
    </div>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div><span class="font-semibold">退货单号：</span>{{ $return->return_no }}</div>
        <div><span class="font-semibold">采购订单：</span>{{ $return->purchaseOrder?->order_no ?? '-' }}</div>
        <div><span class="font-semibold">仓库：</span>{{ $return->warehouse?->name ?? '-' }}</div>
        <div><span class="font-semibold">状态：</span>{{ $return->status ?? '-' }}</div>
        <div><span class="font-semibold">退货日期：</span>{{ $return->return_date ?? '-' }}</div>
        <div><span class="font-semibold">退货原因：</span>{{ $return->reason ?? '-' }}</div>
        <div class="col-span-2"><span class="font-semibold">备注：</span>{{ $return->remark ?? '-' }}</div>
    </div>
    @if($return->lines->isNotEmpty())
    <h3 class="text-lg font-semibold mb-2">退货明细</h3>
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2 text-left">物料</th>
                <th class="border px-4 py-2 text-right">数量</th>
                <th class="border px-4 py-2 text-right">单价</th>
                <th class="border px-4 py-2 text-right">金额</th>
            </tr>
        </thead>
        <tbody>
            @foreach($return->lines as $line)
            <tr class="hover:bg-gray-50">
                <td class="border px-4 py-2">{{ $line->item?->name }}</td>
                <td class="border px-4 py-2 text-right">{{ $line->quantity }}</td>
                <td class="border px-4 py-2 text-right">{{ number_format($line->unit_price ?? 0, 2) }}</td>
                <td class="border px-4 py-2 text-right">{{ number_format($line->quantity * ($line->unit_price ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
