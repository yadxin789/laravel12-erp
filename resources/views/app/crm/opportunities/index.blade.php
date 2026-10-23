@extends('layouts.business')

@section('title', '商机管理')

@section('content')
    @php
        $stageMap = [
            'prospecting' => ['初步接触', 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle'],
            'qualification' => ['需求确认', 'bg-info-subtle text-info-emphasis border-info-subtle'],
            'proposal' => ['方案报价', 'bg-primary-subtle text-primary-emphasis border-primary-subtle'],
            'negotiation' => ['商务谈判', 'bg-warning-subtle text-warning-emphasis border-warning-subtle'],
            'closed_won' => ['已成交', 'bg-success-subtle text-success-emphasis border-success-subtle'],
            'closed_lost' => ['已丢失', 'bg-danger-subtle text-danger-emphasis border-danger-subtle'],
        ];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-bullseye"></i>商机管理</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('app.crm.pipeline') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-filter me-1"></i>销售漏斗
            </a>
            <a href="{{ route('app.crm.leads') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-user-plus me-1"></i>线索列表
            </a>
            <a href="{{ route('app.crm.opportunities.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>新建商机
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>商机名称</th>
                            <th>关联线索/客户</th>
                            <th class="text-end">预计金额</th>
                            <th class="text-center">概率</th>
                            <th>阶段</th>
                            <th>预计成交日</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opps as $opp)
                            @php $st = $stageMap[$opp->stage] ?? [$opp->stage, 'bg-light text-secondary border']; @endphp
                            <tr>
                                <td class="fw-semibold">{{ $opp->name }}</td>
                                <td>{{ $opp->lead?->name ?: ($opp->customer?->name ?: '—') }}</td>
                                <td class="text-end">¥{{ number_format($opp->expected_amount ?? 0, 2) }}</td>
                                <td class="text-center">
                                    <div class="progress" style="height: 6px;" title="{{ $opp->probability }}%">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $opp->probability }}%"></div>
                                    </div>
                                    <span class="small text-muted">{{ $opp->probability }}%</span>
                                </td>
                                <td><span class="badge border {{ $st[1] }}">{{ $st[0] }}</span></td>
                                <td>{{ $opp->expected_close_date ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">暂无商机</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $opps->links() }}
            </div>
        </div>
    </div>
@endsection
