@extends('layouts.business')

@section('title', '批次追踪')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-magnifying-glass"></i>批次追踪</h2>
        <a href="{{ route('app.inventory.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('app.inventory.trace.search') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-12 col-md-4">
                    <label class="form-label" for="batch_number">批次号 <span class="text-danger">*</span></label>
                    <input type="text" id="batch_number" name="batch_number" class="form-control" required
                           placeholder="输入批次号" value="{{ old('batch_number', $batch_number ?? '') }}">
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>查询</button>
                </div>
            </form>
        </div>
    </div>

    @if(isset($transactions) && $transactions->isNotEmpty())
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-clock-rotate-left me-2"></i>批次 {{ $batch_number }} 的流转记录</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>事务单号</th>
                                <th>类型</th>
                                <th>物料</th>
                                <th>仓库</th>
                                <th class="text-end">数量</th>
                                <th>时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr>
                                    <td class="font-monospace small">{{ $transaction->transaction_no }}</td>
                                    <td><span class="badge bg-light text-secondary border">{{ $transaction->transaction_type?->label() ?? '-' }}</span></td>
                                    <td>{{ $transaction->item?->name ?? '-' }}</td>
                                    <td>{{ $transaction->warehouse?->name ?? '-' }}</td>
                                    <td class="text-end">{{ $transaction->quantity }}</td>
                                    <td>{{ $transaction->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif(isset($transactions))
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-0">批次 {{ $batch_number }} 暂无流转记录</p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-0">请输入批次号进行查询</p>
            </div>
        </div>
    @endif
@endsection
