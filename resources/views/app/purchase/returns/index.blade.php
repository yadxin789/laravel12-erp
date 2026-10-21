@extends('layouts.business')

@section('title', '采购退货')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">采购退货</h2>
        <a href="{{ route('app.purchase.returns.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建退货单</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">退货单号</th>
                <th class="text-left py-3">采购订单</th>
                <th class="text-left py-3">仓库</th>
                <th class="text-left py-3">退货日期</th>
                <th class="text-left py-3">原因</th>
                <th class="text-center py-3">状态</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returns as $return)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $return->return_no }}</td>
                <td class="py-3">{{ $return->purchaseOrder?->order_no }}</td>
                <td class="py-3">{{ $return->warehouse?->name }}</td>
                <td class="py-3">{{ $return->return_date }}</td>
                <td class="py-3">{{ $return->reason }}</td>
                <td class="py-3 text-center">{{ $return->status }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('app.purchase.returns.show', $return) }}" class="text-blue-600 hover:text-blue-800">查看</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $returns->links() }}
</div>
@endsection
