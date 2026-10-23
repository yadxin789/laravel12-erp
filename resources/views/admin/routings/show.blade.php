@extends('layouts.admin')

@section('title', '工艺路线详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-diagram-project"></i>工艺路线详情</h2>
        <a href="{{ route('admin.routings.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回列表
        </a>
    </div>

    {{-- 主表信息 --}}
    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>基本信息</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="text-muted small">编码</div>
                    <div class="fw-semibold font-monospace">{{ $routing->code }}</div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="text-muted small">物料</div>
                    <div class="fw-semibold">{{ $routing->item->name ?? '-' }}</div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="text-muted small">版本</div>
                    <div class="fw-semibold">{{ $routing->version }}</div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="text-muted small">状态</div>
                    <div class="fw-semibold"><span class="badge bg-light text-secondary">{{ $routing->status }}</span></div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="text-muted small">生效日期</div>
                    <div class="fw-semibold">{{ $routing->effective_date }}</div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="text-muted small">失效日期</div>
                    <div class="fw-semibold">{{ $routing->expiry_date ?? '-' }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">描述</div>
                    <div class="fw-semibold">{{ $routing->description ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 子表：工序列表 --}}
    <div class="card">
        <div class="card-header"><i class="fa-solid fa-list-check me-2"></i>工序列表</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>工序号</th>
                            <th>工序编码</th>
                            <th>工序名称</th>
                            <th>工作中心</th>
                            <th class="text-end">标准工时</th>
                            <th class="text-end">准备时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($routing->operations as $operation)
                            <tr>
                                <td>{{ $operation->operation_number }}</td>
                                <td class="font-monospace small">{{ $operation->operation_code }}</td>
                                <td>{{ $operation->operation_name }}</td>
                                <td>{{ $operation->workCenter->name ?? '-' }}</td>
                                <td class="text-end">{{ $operation->standard_time }}</td>
                                <td class="text-end">{{ $operation->setup_time }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">该工艺路线暂无工序</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
