@extends('layouts.business')

@section('title', '发货管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">发货管理</h2>
        <a href="{{ route('app.sales.shipments.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建发货单</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">发货单号</th>
                <th class="text-left py-3">销售订单</th>
                <th class="text-left py-3">仓库</th>
                <th class="text-left py-3">发货日期</th>
                <th class="text-center py-3">状态</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shipments as $shipment)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $shipment->shipment_no }}</td>
                <td class="py-3">{{ $shipment->salesOrder?->order_no }}</td>
                <td class="py-3">{{ $shipment->warehouse?->name }}</td>
                <td class="py-3">{{ $shipment->shipment_date }}</td>
                <td class="py-3 text-center">{{ $shipment->status }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('app.sales.shipments.show', $shipment) }}" class="text-blue-600 hover:text-blue-800">查看</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $shipments->links() }}
</div>
@endsection
