@extends('layouts.business')
@section('title', '请购单详情')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">请购单详情 #{{ $requisition->requisition_no }}</h2>
        <a href="{{ route('app.purchase.requisitions.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">返回</a>
    </div>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div><span class="font-semibold">请购单号：</span>{{ $requisition->requisition_no }}</div>
        <div><span class="font-semibold">状态：</span>{{ $requisition->status ?? '-' }}</div>
        <div><span class="font-semibold">请购人：</span>{{ $requisition->requester ?? '-' }}</div>
        <div><span class="font-semibold">仓库：</span>{{ $requisition->warehouse?->name ?? '-' }}</div>
        <div><span class="font-semibold">请购日期：</span>{{ $requisition->requisition_date ?? '-' }}</div>
        <div class="col-span-2"><span class="font-semibold">备注：</span>{{ $requisition->remark ?? '-' }}</div>
    </div>
    @if($requisition->lines->isNotEmpty())
    <h3 class="text-lg font-semibold mb-2">请购明细</h3>
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2 text-left">物料</th>
                <th class="border px-4 py-2 text-right">数量</th>
                <th class="border px-4 py-2 text-left">单位</th>
                <th class="border px-4 py-2 text-left">备注</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requisition->lines as $line)
            <tr class="hover:bg-gray-50">
                <td class="border px-4 py-2">{{ $line->item?->name }}</td>
                <td class="border px-4 py-2 text-right">{{ $line->quantity }}</td>
                <td class="border px-4 py-2">{{ $line->unit ?? '-' }}</td>
                <td class="border px-4 py-2">{{ $line->remark ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
