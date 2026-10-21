@extends('layouts.business')

@section('title', '收货管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">收货管理</h2>
        <a href="{{ route('app.purchase.receipts.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建收货单</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">收货单号</th>
                <th class="text-left py-3">采购订单</th>
                <th class="text-left py-3">仓库</th>
                <th class="text-left py-3">收货日期</th>
                <th class="text-center py-3">状态</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipts as $receipt)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $receipt->receipt_no }}</td>
                <td class="py-3">{{ $receipt->purchaseOrder?->order_no }}</td>
                <td class="py-3">{{ $receipt->warehouse?->name }}</td>
                <td class="py-3">{{ $receipt->receipt_date }}</td>
                <td class="py-3 text-center">{{ $receipt->status }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('app.purchase.receipts.show', $receipt) }}" class="text-blue-600 hover:text-blue-800">查看</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $receipts->links() }}
</div>
@endsection
