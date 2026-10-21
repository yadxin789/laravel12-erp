@extends('layouts.business')

@section('title', '报价单详情')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">报价单: {{ $quotation->quotation_no }}</h2>
        <div>
            @if($quotation->status === 'draft')
            <form action="{{ route('app.sales.quotations.submit', $quotation) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">提交报价</button>
            </form>
            @endif
            @if($quotation->status === 'submitted')
            <form action="{{ route('app.sales.quotations.convert', $quotation) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">生成销售订单</button>
            </form>
            @endif
        </div>
    </div>
    
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div>
            <p class="text-gray-500 text-sm">客户</p>
            <p class="font-medium">{{ $quotation->customer?->name }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">报价日期</p>
            <p class="font-medium">{{ $quotation->quotation_date }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">有效期至</p>
            <p class="font-medium">{{ $quotation->valid_until }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">状态</p>
            <p class="font-medium">{{ $quotation->status }}</p>
        </div>
    </div>

    <h3 class="text-lg font-semibold text-gray-800 mb-4">报价明细</h3>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">物料</th>
                <th class="text-left py-3">规格</th>
                <th class="text-right py-3">数量</th>
                <th class="text-right py-3">单价</th>
                <th class="text-right py-3">金额</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->lines as $line)
            <tr class="border-b">
                <td class="py-3">{{ $line->item?->name }}</td>
                <td class="py-3">{{ $line->item?->spec }}</td>
                <td class="py-3 text-right">{{ $line->quantity }}</td>
                <td class="py-3 text-right">{{ $line->unit_price }}</td>
                <td class="py-3 text-right">{{ $line->amount }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
