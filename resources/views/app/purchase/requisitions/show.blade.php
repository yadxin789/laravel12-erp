@extends('layouts.business')

@section('title', '请购单详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-list"></i>请购单详情 #{{ $requisition->req_no }}</h2>
        <div class="d-flex flex-wrap gap-2">
            @if($requisition->status === 'draft')
                <form action="{{ route('app.purchase.requisitions.submit', $requisition) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>提交审核</button>
                </form>
            @endif
            @if($requisition->status === 'submitted')
                <form action="{{ route('app.purchase.requisitions.approve', $requisition) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i>审核通过</button>
                </form>
            @endif
            <a href="{{ route('app.purchase.requisitions.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="text-muted small">请购单号</div>
                    <div class="fw-semibold font-monospace">{{ $requisition->req_no }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">状态</div>
                    <div><span class="badge bg-light text-secondary border">{{ $requisition->status ?? '-' }}</span></div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">仓库</div>
                    <div class="fw-semibold">{{ $requisition->warehouse?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">请购日期</div>
                    <div class="fw-semibold">{{ $requisition->requisition_date?->format('Y-m-d') ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">创建时间</div>
                    <div class="fw-semibold">{{ $requisition->created_at?->format('Y-m-d H:i') ?? '-' }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">备注</div>
                    <div class="fw-semibold">{{ $requisition->remark ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($requisition->lines->isNotEmpty())
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-list me-2"></i>请购明细</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>物料</th>
                                <th class="text-end">数量</th>
                                <th>单位</th>
                                <th>备注</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requisition->lines as $line)
                                <tr>
                                    <td>{{ $line->item?->name }}</td>
                                    <td class="text-end">{{ $line->quantity }}</td>
                                    <td>{{ $line->unit ?? '-' }}</td>
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
