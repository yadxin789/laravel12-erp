@extends('layouts.business')

@section('title', '库存报表')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-boxes-stacked"></i>库存报表</h2>
        <a href="{{ route('app.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>报表中心
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-ranking-star me-2"></i>库存数量 Top 20</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>物料编码</th>
                            <th>名称</th>
                            <th class="text-end">总库存</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topItems as $row)
                            <tr>
                                <td class="font-monospace small">{{ $row->item?->code }}</td>
                                <td>{{ $row->item?->name }}</td>
                                <td class="text-end">{{ $row->qty }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="erp-empty">暂无库存数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-danger-subtle">
        <div class="card-header text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>低库存预警</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>物料</th>
                            <th class="text-end">当前库存</th>
                            <th class="text-end">安全库存</th>
                            <th class="text-end">缺口</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStock as $item)
                            <tr>
                                <td>
                                    <span class="font-monospace small text-muted">{{ $item->code }}</span>
                                    {{ $item->name }}
                                </td>
                                <td class="text-end text-danger fw-semibold">{{ $item->total_qty ?? 0 }}</td>
                                <td class="text-end">{{ $item->safety_stock }}</td>
                                <td class="text-end text-danger">{{ ($item->total_qty ?? 0) - $item->safety_stock }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="erp-empty">无低库存物料</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
