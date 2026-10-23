@extends('layouts.business')

@section('title', '缺陷管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-triangle-exclamation"></i>缺陷管理</h2>
        <span class="text-muted small">缺陷代码字典 + 最近缺陷记录</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-primary-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">缺陷代码总数</div>
                    <div class="fs-3 fw-bold text-primary">{{ $defectCodes->total() }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-warning-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">最近缺陷记录</div>
                    <div class="fs-3 fw-bold text-warning-emphasis">{{ $defectRecords->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-success-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">启用中代码</div>
                    <div class="fs-3 fw-bold text-success">{{ $defectCodes->where('active', true)->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-book me-2"></i>缺陷代码字典</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>代码</th>
                            <th>名称</th>
                            <th>分类</th>
                            <th>描述</th>
                            <th class="text-center">状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($defectCodes as $code)
                            <tr>
                                <td class="font-monospace small">{{ $code->code }}</td>
                                <td>{{ $code->name }}</td>
                                <td><span class="badge bg-light text-secondary border">{{ $code->category }}</span></td>
                                <td class="text-muted">{{ $code->description ?? '—' }}</td>
                                <td class="text-center">
                                    @if($code->active)
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">启用</span>
                                    @else
                                        <span class="badge bg-light text-secondary border">停用</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="erp-empty">暂无缺陷代码数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $defectCodes->links() }}
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-clock-rotate-left me-2"></i>最近缺陷记录</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>记录号</th>
                            <th>缺陷类型</th>
                            <th>缺陷代码</th>
                            <th>物料</th>
                            <th class="text-end">数量</th>
                            <th>处置方式</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($defectRecords as $record)
                            <tr>
                                <td class="font-monospace small">{{ $record->record_no ?? '—' }}</td>
                                <td>{{ $record->defect_type ?? '—' }}</td>
                                <td>{{ $record->defectCode?->name ?? '—' }}</td>
                                <td>{{ $record->item?->name ?? '—' }}</td>
                                <td class="text-end">{{ $record->quantity }}</td>
                                <td>{{ $record->disposition ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">暂无缺陷记录</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
