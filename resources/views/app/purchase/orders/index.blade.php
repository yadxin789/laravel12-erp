@extends('layouts.business')

@section('title', '采购订单')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">采购订单</h2>
        <a href="{{ route('app.purchase.orders.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建订单</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">订单号</th>
                <th class="text-left py-3">供应商</th>
                <th class="text-left py-3">订单日期</th>
                <th class="text-right py-3">金额</th>
                <th class="text-center py-3">状态</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $order->order_no }}</td>
                <td class="py-3">{{ $order->supplier?->name }}</td>
                <td class="py-3">{{ $order->order_date }}</td>
                <td class="py-3 text-right">{{ $order->amount }}</td>
                <td class="py-3 text-center">{{ $order->status }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('app.purchase.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800">查看</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $orders->links() }}
</div>
@endsection
