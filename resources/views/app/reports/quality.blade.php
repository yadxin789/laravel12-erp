@extends('layouts.business')

@section('title', '质量报表')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-check"></i>质量报表 {{ $year }}年</h2>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <form method="GET" action="{{ route('app.reports.quality') }}" class="d-flex gap-2 align-items-center">
                <input type="number" name="year" class="form-control" style="width: 110px;" value="{{ $year }}" min="2000" max="2100">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass me-1"></i>查询</button>
            </form>
            <a href="{{ route('app.reports.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>报表中心
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-calendar-days me-2"></i>按月检验结果</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>月份</th>
                            <th class="text-end">总检验</th>
                            <th class="text-end">合格</th>
                            <th class="text-end">不合格</th>
                            <th class="text-end">合格率</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthly as $m)
                            <tr>
                                <td>{{ $m->month }}月</td>
                                <td class="text-end">{{ $m->total }}</td>
                                <td class="text-end text-success">{{ $m->passed }}</td>
                                <td class="text-end text-danger">{{ $m->failed }}</td>
                                <td class="text-end">{{ $m->total > 0 ? round($m->passed / $m->total * 100, 1) : 0 }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="erp-empty">{{ $year }} 年暂无检验数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-list me-2"></i>按检验类型</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>检验类型</th>
                            <th class="text-end">数量</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byType as $row)
                            <tr>
                                <td>{{ \App\Enums\InspectionType::tryFrom((string) $row->inspection_type)?->label() ?? $row->inspection_type }}</td>
                                <td class="text-end">{{ $row->total }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="erp-empty">暂无检验类型数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
