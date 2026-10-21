@extends('layouts.business')

@section('title', '报价单')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">报价单</h2>
        <a href="{{ route('app.sales.quotations.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建报价单</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">报价单号</th>
                <th class="text-left py-3">客户</th>
                <th class="text-left py-3">报价日期</th>
                <th class="text-left py-3">有效期至</th>
                <th class="text-center py-3">状态</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotations as $quotation)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $quotation->quotation_no }}</td>
                <td class="py-3">{{ $quotation->customer?->name }}</td>
                <td class="py-3">{{ $quotation->quotation_date }}</td>
                <td class="py-3">{{ $quotation->valid_until }}</td>
                <td class="py-3 text-center">{{ $quotation->status }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('app.sales.quotations.show', $quotation) }}" class="text-blue-600 hover:text-blue-800">查看</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $quotations->links() }}
</div>
@endsection
