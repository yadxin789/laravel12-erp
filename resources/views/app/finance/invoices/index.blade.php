@extends('layouts.business')

@section('title', '应收管理')

@section('content')
    @php
        $statusMap = [
            'unpaid' => ['未收款', 'bg-danger-subtle text-danger-emphasis border-danger-subtle'],
            'partial' => ['部分收款', 'bg-warning-subtle text-warning-emphasis border-warning-subtle'],
            'paid' => ['已收款', 'bg-success-subtle text-success-emphasis border-success-subtle'],
            'overdue' => ['逾期', 'bg-dark-subtle text-dark-emphasis border-dark-subtle'],
            'cancelled' => ['已取消', 'bg-light text-secondary border'],
        ];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-file-invoice-dollar"></i>客户发票（应收）</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('app.finance.statements') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-scale-balanced me-1"></i>对账单
            </a>
            <a href="{{ route('app.finance.invoices.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>新建发票
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>发票号</th>
                            <th>客户</th>
                            <th>开票日期</th>
                            <th>到期日</th>
                            <th class="text-end">金额</th>
                            <th class="text-end">已收</th>
                            <th class="text-end">余额</th>
                            <th class="text-center">状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            @php
                                $balance = ($inv->total_amount ?? 0) - ($inv->paid_amount ?? 0);
                                $s = $statusMap[$inv->status] ?? ['—', 'bg-light text-secondary border'];
                            @endphp
                            <tr>
                                <td class="font-monospace small">{{ $inv->invoice_no }}</td>
                                <td>{{ $inv->customer?->name ?? '-' }}</td>
                                <td>{{ $inv->invoice_date ?? '-' }}</td>
                                <td>
                                    {{ $inv->due_date ?? '-' }}
                                    @if($inv->due_date && $inv->due_date < now()->toDateString() && $inv->status !== 'paid')
                                        <span class="badge bg-danger ms-1">已逾期</span>
                                    @endif
                                </td>
                                <td class="text-end">¥{{ number_format($inv->total_amount ?? 0, 2) }}</td>
                                <td class="text-end text-success">¥{{ number_format($inv->paid_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-semibold {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                    ¥{{ number_format($balance, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge border {{ $s[1] }}">{{ $s[0] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="erp-empty">暂无客户发票</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
@endsection
