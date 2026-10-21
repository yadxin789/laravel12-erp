@extends('layouts.business')

@section('title', '采购订单详情')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">采购订单: {{ $order->order_no }}</h2>
        <div>
            @if($order->status === 'draft')
            <form action="{{ route('app.purchase.orders.submit', $order) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">提交审核</button>
            </form>
            @elseif($order->status === 'submitted')
            <form action="{{ route('app.purchase.orders.approve', $order) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">审核通过</button>
            </form>
            @endif
            @if($order->status !== 'cancelled')
            <form action="{{ route('app.purchase.orders.cancel', $order) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">取消订单</button>
            </form>
            @endif
        </div>
    </div>
    
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div>
            <p class="text-gray-500 text-sm">供应商</p>
            <p class="font-medium">{{ $order->supplier?->name }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">订单日期</p>
            <p class="font-medium">{{ $order->order_date }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">交货日期</p>
            <p class="font-medium">{{ $order->delivery_date }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">状态</p>
            <p class="font-medium">{{ $order->status }}</p>
        </div>
    </div>

    <h3 class="text-lg font-semibold text-gray-800 mb-4">订单明细</h3>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">物料</th>
                <th class="text-left py-3">规格</th>
                <th class="text-right py-3">数量</th>
                <th class="text-right py-3">单价</th>
                <th class="text-right py-3">金额</th>
                <th class="text-left py-3">仓库</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->lines as $line)
            <tr class="border-b">
                <td class="py-3">{{ $line->item?->name }}</td>
                <td class="py-3">{{ $line->item?->spec }}</td>
                <td class="py-3 text-right">{{ $line->quantity }}</td>
                <td class="py-3 text-right">{{ $line->unit_price }}</td>
                <td class="py-3 text-right">{{ $line->amount }}</td>
                <td class="py-3">{{ $line->warehouse?->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
