@extends('layouts.business')

@section('title', '成品入库')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">成品入库</h2>
        <a href="{{ route('app.production.receipts.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建入库单</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">入库单号</th>
                <th class="text-left py-3">生产工单</th>
                <th class="text-left py-3">仓库</th>
                <th class="text-right py-3">数量</th>
                <th class="text-left py-3">批次号</th>
                <th class="text-left py-3">入库日期</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipts as $receipt)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $receipt->receipt_no }}</td>
                <td class="py-3">{{ $receipt->productionOrder?->order_no }}</td>
                <td class="py-3">{{ $receipt->warehouse?->name }}</td>
                <td class="py-3 text-right">{{ $receipt->quantity }}</td>
                <td class="py-3">{{ $receipt->batch_number ?? '-' }}</td>
                <td class="py-3">{{ $receipt->receipt_date }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $receipts->links() }}
</div>
@endsection
