@extends('layouts.business')
@section('title', '生产汇报详情')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">生产汇报详情</h2>
        <a href="{{ route('app.production.reports.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">返回</a>
    </div>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div><span class="font-semibold">工单号：</span>{{ $report->productionOrder?->order_no ?? '-' }}</div>
        <div><span class="font-semibold">汇报日期：</span>{{ $report->report_date ?? '-' }}</div>
        <div><span class="font-semibold">汇报数量：</span>{{ $report->quantity }}</div>
        <div><span class="font-semibold">合格数量：</span>{{ $report->qualified_quantity ?? 0 }}</div>
        <div><span class="font-semibold">不合格数量：</span>{{ $report->defective_quantity ?? 0 }}</div>
        <div><span class="font-semibold">报废数量：</span>{{ $report->scrap_quantity ?? 0 }}</div>
        <div class="col-span-2"><span class="font-semibold">备注：</span>{{ $report->remark ?? '-' }}</div>
    </div>
</div>
@endsection
