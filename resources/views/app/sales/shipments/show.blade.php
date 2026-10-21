@extends('layouts.business')
@section('title', '发货单详情')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">发货单详情 #{{ $shipment->shipment_no }}</h2>
        <a href="{{ route('app.sales.shipments.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">返回</a>
    </div>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div><span class="font-semibold">发货单号：</span>{{ $shipment->shipment_no }}</div>
        <div><span class="font-semibold">销售订单：</span>{{ $shipment->salesOrder?->order_no ?? '-' }}</div>
        <div><span class="font-semibold">客户：</span>{{ $shipment->customer?->name ?? '-' }}</div>
        <div><span class="font-semibold">仓库：</span>{{ $shipment->warehouse?->name ?? '-' }}</div>
        <div><span class="font-semibold">状态：</span>{{ $shipment->status ?? '-' }}</div>
        <div><span class="font-semibold">发货日期：</span>{{ $shipment->shipment_date ?? '-' }}</div>
        <div class="col-span-2"><span class="font-semibold">备注：</span>{{ $shipment->remark ?? '-' }}</div>
    </div>
    @if($shipment->lines->isNotEmpty())
    <h3 class="text-lg font-semibold mb-2">发货明细</h3>
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2 text-left">物料</th>
                <th class="border px-4 py-2 text-right">订单数量</th>
                <th class="border px-4 py-2 text-right">发货数量</th>
                <th class="border px-4 py-2 text-left">批次号</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shipment->lines as $line)
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
