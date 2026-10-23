@extends('layouts.business')

@section('title', '应付管理')

@section('content')
    @php
        $statusMap = [
            'unpaid' => ['未付款', 'bg-danger-subtle text-danger-emphasis border-danger-subtle'],
            'partial' => ['部分付款', 'bg-warning-subtle text-warning-emphasis border-warning-subtle'],
            'paid' => ['已付款', 'bg-success-subtle text-success-emphasis border-success-subtle'],
            'overdue' => ['逾期', 'bg-dark-subtle text-dark-emphasis border-dark-subtle'],
            'cancelled' => ['已取消', 'bg-light text-secondary border'],
        ];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-money-bill-wave"></i>供应商账单（应付）</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('app.finance.statements') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-scale-balanced me-1"></i>对账单
            </a>
            <a href="{{ route('app.finance.bills.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>新建账单
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>账单号</th>
                            <th>供应商</th>
                            <th>账单日期</th>
                            <th>到期日</th>
                            <th class="text-end">金额</th>
                            <th class="text-end">已付</th>
                            <th class="text-end">余额</th>
                            <th class="text-center">状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bills as $bill)
                            @php
                                $balance = ($bill->total_amount ?? 0) - ($bill->paid_amount ?? 0);
                                $s = $statusMap[$bill->status] ?? ['—', 'bg-light text-secondary border'];
                            @endphp
                            <tr>
                                <td class="font-monospace small">{{ $bill->bill_no }}</td>
                                <td>{{ $bill->supplier?->name ?? '-' }}</td>
                                <td>{{ $bill->bill_date ?? '-' }}</td>
                                <td>
                                    {{ $bill->due_date ?? '-' }}
                                    @if($bill->due_date && $bill->due_date < now()->toDateString() && $bill->status !== 'paid')
                                        <span class="badge bg-danger ms-1">已逾期</span>
                                    @endif
                                </td>
                                <td class="text-end">¥{{ number_format($bill->total_amount ?? 0, 2) }}</td>
                                <td class="text-end text-success">¥{{ number_format($bill->paid_amount ?? 0, 2) }}</td>
                                <td class="text-end fw-semibold {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                    ¥{{ number_format($balance, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge border {{ $s[1] }}">{{ $s[0] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="erp-empty">暂无供应商账单</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $bills->links() }}
            </div>
        </div>
    </div>
@endsection
