@extends('layouts.business')

@section('title', '库存预警分析')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-boxes-stacked"></i>库存预警分析</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    @if(!empty($summary))
        <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $summary }}</div>
    @endif

    @if(!empty($items) && $items->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header"><i class="fa-solid fa-list me-2"></i>低于安全库存物料</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>物料</th>
                                <th class="text-end">现有库存</th>
                                <th class="text-end">安全库存</th>
                                <th class="text-end">缺口</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>
                                        <span class="font-monospace small text-muted">{{ $item->code }}</span>
                                        {{ $item->name }}
                                    </td>
                                    <td class="text-end">{{ $item->qty ?? 0 }}</td>
                                    <td class="text-end">{{ $item->safety_stock ?? 0 }}</td>
                                    <td class="text-end text-danger fw-semibold">{{ ($item->qty ?? 0) - ($item->safety_stock ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if(!empty($suggestions))
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-lightbulb me-2"></i>AI 补货建议</div>
            <div class="card-body">
                <div class="vstack gap-3">
                    @foreach($suggestions as $s)
                        <div class="border rounded p-3">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="fw-semibold">{{ $s['name'] ?? '' }}</span>
                                <span class="text-primary">建议补货 {{ $s['suggest_qty'] ?? '' }}</span>
                                <span class="badge {{ ($s['urgency'] ?? '') === '高' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                    {{ $s['urgency'] ?? '' }}
                                </span>
                            </div>
                            <div class="text-muted small mt-1">{{ $s['note'] ?? '' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection
