@extends('layouts.business')

@section('title', '检验管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-check"></i>检验管理</h2>
        <div class="d-flex flex-wrap gap-2">
            @can('create quality inspections')
                <a href="{{ route('app.quality.inspections.create') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-1"></i>新建检验单
                </a>
            @endcan
            @can('view quality reports')
                <a href="{{ route('app.quality.reports') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-chart-column me-1"></i>质量报告
                </a>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>检验单号</th>
                            <th>检验类型</th>
                            <th>物料</th>
                            <th>批次号</th>
                            <th class="text-end">检验数量</th>
                            <th class="text-center">结果</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inspections as $inspection)
                            @php
                                $result = $inspection->result;
                            @endphp
                            <tr>
                                <td class="font-monospace small">{{ $inspection->inspection_no }}</td>
                                <td>{{ $inspection->inspection_type?->label() ?? '-' }}</td>
                                <td>{{ $inspection->item?->name ?? '-' }}</td>
                                <td>{{ $inspection->batch_number ?? '-' }}</td>
                                <td class="text-end">{{ $inspection->inspected_size }}</td>
                                <td class="text-center">
                                    @if($result === \App\Enums\QualityResult::Pass)
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">合格</span>
                                    @elseif($result === \App\Enums\QualityResult::Fail)
                                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">不合格</span>
                                    @elseif($result === \App\Enums\QualityResult::Conditional)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">有条件放行</span>
                                    @elseif($result === \App\Enums\QualityResult::Waived)
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">豁免接收</span>
                                    @else
                                        <span class="badge bg-light text-secondary border">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border">{{ $inspection->status?->label() ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.quality.inspections.show', $inspection) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-regular fa-eye"></i>查看
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="erp-empty">暂无检验单</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $inspections->links() }}
            </div>
        </div>
    </div>
@endsection
