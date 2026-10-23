@extends('layouts.business')

@section('title', '领料单详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-box-open"></i>领料单详情 #{{ $issue->issue_no }}</h2>
        <div class="d-flex flex-wrap gap-2">
            @if($issue->status === 'draft')
                <form action="{{ route('app.production.issues.confirm', $issue) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('确认领料后将扣减库存，确定继续吗？')">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i>确认领料</button>
                </form>
            @endif
            <a href="{{ route('app.production.issues.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="text-muted small">领料单号</div>
                    <div class="fw-semibold font-monospace">{{ $issue->issue_no }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">生产工单</div>
                    <div class="fw-semibold">{{ $issue->productionOrder?->order_no ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">仓库</div>
                    <div class="fw-semibold">{{ $issue->warehouse?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">状态</div>
                    <div><span class="badge bg-light text-secondary border">{{ $issue->status ?? '-' }}</span></div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">领料日期</div>
                    <div class="fw-semibold">{{ $issue->issue_date ?? '-' }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">备注</div>
                    <div class="fw-semibold">{{ $issue->remark ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($issue->lines->isNotEmpty())
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-list me-2"></i>领料明细</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>物料</th>
                                <th class="text-end">领用数量</th>
                                <th>批次号</th>
                                <th>备注</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($issue->lines as $line)
                                <tr>
                                    <td>{{ $line->item?->name }}</td>
                                    <td class="text-end">{{ $line->quantity }}</td>
                                    <td>{{ $line->batch_number ?? '-' }}</td>
                                    <td>{{ $line->remark ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
