@extends('layouts.business')

@section('title', '销售统计报表')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-money-bill-trend-up"></i>销售统计 {{ $year }}年</h2>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <form method="GET" action="{{ route('app.reports.sales') }}" class="d-flex gap-2 align-items-center">
                <input type="number" name="year" class="form-control" style="width: 110px;" value="{{ $year }}" min="2000" max="2100">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass me-1"></i>查询</button>
            </form>
            <a href="{{ route('app.reports.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>报表中心
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-calendar-days me-2"></i>按月统计</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>月份</th>
                            <th class="text-end">订单数</th>
                            <th class="text-end">销售金额</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthly as $m)
                            <tr>
                                <td>{{ $m->month }}月</td>
                                <td class="text-end">{{ $m->order_count }}</td>
                                <td class="text-end">¥{{ number_format($m->total_amount ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="erp-empty">{{ $year }} 年暂无销售数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-ranking-star me-2"></i>客户销售 Top 10</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>客户</th>
                            <th class="text-end">订单数</th>
                            <th class="text-end">累计金额</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byCustomer as $row)
                            <tr>
                                <td>{{ $row->customer?->name ?? '-' }}</td>
                                <td class="text-end">{{ $row->cnt }}</td>
                                <td class="text-end">¥{{ number_format($row->total ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="erp-empty">暂无客户销售数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
