@extends('layouts.business')

@section('title', '生产汇报')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">生产汇报</h2>
        <a href="{{ route('app.production.reports.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建汇报</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">汇报单号</th>
                <th class="text-left py-3">生产工单</th>
                <th class="text-right py-3">汇报数量</th>
                <th class="text-right py-3">合格数量</th>
                <th class="text-right py-3">报废数量</th>
                <th class="text-left py-3">汇报日期</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $report)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $report->report_no }}</td>
                <td class="py-3">{{ $report->productionOrder?->order_no }}</td>
                <td class="py-3 text-right">{{ $report->reported_quantity }}</td>
                <td class="py-3 text-right">{{ $report->reported_quantity - $report->rejected_quantity - $report->scrapped_quantity }}</td>
                <td class="py-3 text-right">{{ $report->scrapped_quantity }}</td>
                <td class="py-3">{{ $report->report_date }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $reports->links() }}
</div>
@endsection
