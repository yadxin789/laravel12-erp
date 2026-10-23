@extends('layouts.business')

@section('title', '收付款记录')

@section('content')
    @php
        $methodMap = [
            'bank_transfer' => '银行转账',
            'cash' => '现金',
            'check' => '支票',
            'alipay' => '支付宝',
            'wechat' => '微信',
        ];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-money-bill-transfer"></i>收付款记录</h2>
        <a href="{{ route('app.finance.payments.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>登记收付款
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>单号</th>
                            <th>类型</th>
                            <th>往来单位</th>
                            <th>日期</th>
                            <th class="text-end">金额</th>
                            <th>方式</th>
                            <th>流水号</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                            <tr>
                                <td class="font-monospace small">{{ $p->payment_no }}</td>
                                <td>
                                    @if($p->type === 'receipt')
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">收款</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">付款</span>
                                    @endif
                                </td>
                                <td>{{ $p->customer?->name ?: ($p->supplier?->name ?: '-') }}</td>
                                <td>{{ $p->payment_date ?? '-' }}</td>
                                <td class="text-end fw-semibold">¥{{ number_format($p->amount ?? 0, 2) }}</td>
                                <td>{{ $methodMap[$p->method] ?? ($p->method ?: '-') }}</td>
                                <td class="text-muted small">{{ $p->reference_no ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">暂无收付款记录</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
@endsection
