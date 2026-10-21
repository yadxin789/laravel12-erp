@extends('layouts.business')

@section('title', '生产工单')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">生产工单</h2>
        <a href="{{ route('app.production.orders.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建工单</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">工单号</th>
                <th class="text-left py-3">产品</th>
                <th class="text-right py-3">数量</th>
                <th class="text-left py-3">开始日期</th>
                <th class="text-left py-3">结束日期</th>
                <th class="text-center py-3">状态</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $order->order_no }}</td>
                <td class="py-3">{{ $order->item?->name }}</td>
                <td class="py-3 text-right">{{ $order->quantity }}</td>
                <td class="py-3">{{ $order->start_date }}</td>
                <td class="py-3">{{ $order->end_date }}</td>
                <td class="py-3 text-center">{{ $order->status }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('app.production.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800">查看</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $orders->links() }}
</div>
@endsection
