@extends('layouts.business')

@section('title', 'CRM 线索')

@section('content')
    @php
        $statusMap = [
            'new' => ['新建', 'bg-primary-subtle text-primary-emphasis border-primary-subtle'],
            'contacted' => ['已联系', 'bg-info-subtle text-info-emphasis border-info-subtle'],
            'qualified' => ['已确认', 'bg-success-subtle text-success-emphasis border-success-subtle'],
            'opportunity' => ['已转商机', 'bg-warning-subtle text-warning-emphasis border-warning-subtle'],
            'lost' => ['已流失', 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle'],
            'converted' => ['已转化', 'bg-success-subtle text-success-emphasis border-success-subtle'],
        ];
        $sourceMap = [
            'manual' => '手动录入',
            'website' => '网站咨询',
            'exhibition' => '展会',
            'referral' => '客户推荐',
            'cold_call' => '陌拜/电话',
        ];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-user-plus"></i>销售线索</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('app.crm.pipeline') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-filter me-1"></i>销售漏斗
            </a>
            <a href="{{ route('app.crm.opportunities') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-bullseye me-1"></i>商机管理
            </a>
            <a href="{{ route('app.crm.leads.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>新建线索
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>名称</th>
                            <th>联系人</th>
                            <th>电话</th>
                            <th>来源</th>
                            <th class="text-center">状态</th>
                            <th>负责人</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            @php $s = $statusMap[$lead->status] ?? [$lead->status, 'bg-light text-secondary border']; @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('app.crm.leads.show', $lead) }}" class="fw-semibold text-decoration-none">{{ $lead->name }}</a>
                                    @if($lead->company)
                                        <div class="small text-muted">{{ $lead->company }}</div>
                                    @endif
                                </td>
                                <td>{{ $lead->contact_person ?: '—' }}</td>
                                <td>{{ $lead->phone ?: '—' }}</td>
                                <td class="text-muted">{{ $sourceMap[$lead->source] ?? ($lead->source ?: '—') }}</td>
                                <td class="text-center"><span class="badge border {{ $s[1] }}">{{ $s[0] }}</span></td>
                                <td>{{ $lead->assignedTo?->name ?: '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('app.crm.leads.show', $lead) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-eye me-1"></i>查看
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">暂无销售线索</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $leads->links() }}
            </div>
        </div>
    </div>
@endsection
