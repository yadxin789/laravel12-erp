@extends('layouts.business')

@section('title', '对账单')

@section('content')
    @php
        $arTotal = $arSummary->sum('total');
        $arPaid = $arSummary->sum('paid');
        $arBalance = $arSummary->sum('balance');
        $apTotal = $apSummary->sum('total');
        $apPaid = $apSummary->sum('paid');
        $apBalance = $apSummary->sum('balance');
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-scale-balanced"></i>往来对账单</h2>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-primary-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">应收余额</div>
                    <div class="fs-3 fw-bold text-primary">¥{{ number_format($arBalance, 2) }}</div>
                    <div class="small text-muted">应收 ¥{{ number_format($arTotal, 2) }} / 已收 ¥{{ number_format($arPaid, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-danger-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">应付余额</div>
                    <div class="fs-3 fw-bold text-danger">¥{{ number_format($apBalance, 2) }}</div>
                    <div class="small text-muted">应付 ¥{{ number_format($apTotal, 2) }} / 已付 ¥{{ number_format($apPaid, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-success-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">净头寸（应收 − 应付）</div>
                    <div class="fs-3 fw-bold {{ $arBalance - $apBalance >= 0 ? 'text-success' : 'text-danger' }}">
                        ¥{{ number_format($arBalance - $apBalance, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header text-success"><i class="fa-solid fa-arrow-down me-2"></i>应收账款（按客户）</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm">
                            <thead>
                                <tr>
                                    <th>客户</th>
                                    <th class="text-end">应收</th>
                                    <th class="text-end">已收</th>
                                    <th class="text-end">余额</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($arSummary as $row)
                                    <tr>
                                        <td>{{ $row->customer?->name ?? '-' }}</td>
                                        <td class="text-end">¥{{ number_format($row->total ?? 0, 2) }}</td>
                                        <td class="text-end text-success">¥{{ number_format($row->paid ?? 0, 2) }}</td>
                                        <td class="text-end fw-semibold {{ ($row->balance ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                            ¥{{ number_format($row->balance ?? 0, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="erp-empty">暂无应收数据</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header text-primary"><i class="fa-solid fa-arrow-up me-2"></i>应付账款（按供应商）</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm">
                            <thead>
                                <tr>
                                    <th>供应商</th>
                                    <th class="text-end">应付</th>
                                    <th class="text-end">已付</th>
                                    <th class="text-end">余额</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apSummary as $row)
                                    <tr>
                                        <td>{{ $row->supplier?->name ?? '-' }}</td>
                                        <td class="text-end">¥{{ number_format($row->total ?? 0, 2) }}</td>
                                        <td class="text-end text-success">¥{{ number_format($row->paid ?? 0, 2) }}</td>
                                        <td class="text-end fw-semibold {{ ($row->balance ?? 0) > 0 ? 'text-danger' : 'text-primary' }}">
                                            ¥{{ number_format($row->balance ?? 0, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="erp-empty">暂无应付数据</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
