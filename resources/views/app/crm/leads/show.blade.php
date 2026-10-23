@extends('layouts.business')

@section('title', '线索详情')

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
        $typeMap = ['note' => '备注', 'call' => '电话', 'email' => '邮件', 'meeting' => '拜访'];
        $s = $statusMap[$lead->status] ?? [$lead->status, 'bg-light text-secondary border'];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-user-plus"></i>{{ $lead->name }}</h2>
        <a href="{{ route('app.crm.leads') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="card mb-4">
                <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>线索信息</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">联系人</div>
                            <div class="fw-semibold">{{ $lead->contact_person ?: '—' }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">电话</div>
                            <div class="fw-semibold">{{ $lead->phone ?: '—' }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">邮箱</div>
                            <div class="fw-semibold">{{ $lead->email ?: '—' }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">公司</div>
                            <div class="fw-semibold">{{ $lead->company ?: '—' }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">来源</div>
                            <div class="fw-semibold">{{ $sourceMap[$lead->source] ?? ($lead->source ?: '—') }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">状态</div>
                            <div><span class="badge border {{ $s[1] }}">{{ $s[0] }}</span></div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">备注</div>
                            <div class="fw-semibold">{{ $lead->remark ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fa-solid fa-comments me-2"></i>跟进记录</div>
                <div class="card-body">
                    @forelse($lead->followUps as $fu)
                        <div class="border-start border-3 border-primary ps-3 py-2 mb-2">
                            <div class="text-muted small">
                                {{ $fu->created_at?->format('Y-m-d H:i') ?? '-' }}
                                <span class="badge bg-light text-secondary border ms-1">{{ $typeMap[$fu->type] ?? $fu->type }}</span>
                            </div>
                            <p class="mb-0">{{ $fu->content }}</p>
                        </div>
                    @empty
                        <p class="erp-empty mb-0">暂无跟进记录</p>
                    @endforelse

                    <form method="POST" action="{{ route('app.crm.leads.follow-up', $lead) }}" class="d-flex flex-wrap gap-2 mt-3">
                        @csrf
                        <select name="type" class="form-select w-auto">
                            <option value="note">备注</option>
                            <option value="call">电话</option>
                            <option value="email">邮件</option>
                            <option value="meeting">拜访</option>
                        </select>
                        <input type="text" name="content" required placeholder="填写跟进内容..." class="form-control flex-fill">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus me-1"></i>添加
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header"><i class="fa-solid fa-bullseye me-2"></i>关联商机</div>
                <div class="card-body">
                    @forelse($lead->opportunities as $opp)
                        <div class="border rounded p-2 mb-2">
                            <div class="fw-semibold small">{{ $opp->name }}</div>
                            <div class="text-muted small">
                                ¥{{ number_format($opp->expected_amount ?? 0, 2) }} · {{ $opp->probability }}% · {{ $opp->stage }}
                            </div>
                        </div>
                    @empty
                        <p class="erp-empty mb-0">暂无关联商机</p>
                    @endforelse
                    <a href="{{ route('app.crm.opportunities.create') }}" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="fa-solid fa-plus me-1"></i>新建商机
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
