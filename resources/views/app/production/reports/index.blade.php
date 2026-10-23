@extends('layouts.business')

@section('title', '生产汇报')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-check"></i>生产汇报</h2>
        <a href="{{ route('app.production.reports.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建汇报
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>汇报单号</th>
                            <th>生产工单</th>
                            <th class="text-end">汇报数量</th>
                            <th class="text-end">返工数量</th>
                            <th class="text-end">报废数量</th>
                            <th>汇报日期</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td class="font-monospace small">{{ $report->report_no }}</td>
                                <td>{{ $report->productionOrder?->order_no ?? '-' }}</td>
                                <td class="text-end">{{ $report->quantity }}</td>
                                <td class="text-end">{{ $report->rework_quantity }}</td>
                                <td class="text-end">{{ $report->scrap_quantity }}</td>
                                <td>{{ $report->report_date }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">暂无生产汇报记录</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
@endsection
