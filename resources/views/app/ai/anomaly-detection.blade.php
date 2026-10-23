@extends('layouts.business')

@section('title', '异常订单检测')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-triangle-exclamation"></i>异常订单检测</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if(($total ?? 0) == 0)
                <p class="erp-empty mb-0">本周未检测到异常订单</p>
            @else
                <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>检测到 {{ $total }} 个异常订单</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>类型</th>
                                <th>订单号</th>
                                <th>详情</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($flags as $f)
                                <tr>
                                    <td><span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">{{ $f['type'] ?? '-' }}</span></td>
                                    <td class="font-monospace small">{{ $f['order_no'] ?? '-' }}</td>
                                    <td class="text-muted">{{ $f['detail'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
