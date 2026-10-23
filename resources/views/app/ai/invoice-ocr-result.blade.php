@extends('layouts.business')

@section('title', '发票识别结果')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-file-invoice"></i>发票识别结果</h2>
        <a href="{{ route('app.ai.invoice-ocr') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-rotate-right me-1"></i>重新上传
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>发票信息</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="text-muted small">供应商</div>
                    <div class="fw-semibold">{{ $data['supplier_name'] ?? '—' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">发票日期</div>
                    <div class="fw-semibold">{{ $data['invoice_date'] ?? '—' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">发票号码</div>
                    <div class="fw-semibold">{{ $data['invoice_no'] ?? '—' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">总金额</div>
                    <div class="fw-semibold text-danger">¥{{ number_format($data['total_amount'] ?? 0, 2) }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">税额</div>
                    <div class="fw-semibold">¥{{ number_format($data['tax_amount'] ?? 0, 2) }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">价税合计</div>
                    <div class="fw-semibold">¥{{ number_format(($data['total_amount'] ?? 0) + ($data['tax_amount'] ?? 0), 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($data['items']))
        <div class="card mb-4">
            <div class="card-header"><i class="fa-solid fa-list me-2"></i>明细行</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>物料名称</th>
                                <th>规格</th>
                                <th class="text-end">数量</th>
                                <th class="text-end">单价</th>
                                <th class="text-end">金额</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['items'] as $item)
                                <tr>
                                    <td>{{ $item['item_name'] ?? '—' }}</td>
                                    <td>{{ $item['spec'] ?? '—' }}</td>
                                    <td class="text-end">{{ $item['quantity'] ?? '—' }}</td>
                                    <td class="text-end">{{ $item['unit_price'] ?? '—' }}</td>
                                    <td class="text-end">{{ $item['amount'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if(!empty($imagePath))
        <div class="card mb-4">
            <div class="card-header"><i class="fa-regular fa-image me-2"></i>发票原图</div>
            <div class="card-body text-center">
                <img src="{{ asset('storage/' . $imagePath) }}" class="img-fluid rounded border" style="max-height: 20rem;" alt="发票原图">
            </div>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('app.purchase.orders.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>手动创建采购订单
        </a>
        <a href="{{ route('app.ai.invoice-ocr') }}" class="btn btn-light">识别下一张</a>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <details>
                <summary class="text-muted small" style="cursor: pointer;">查看 AI 原始输出</summary>
                <pre class="mt-2 p-3 bg-body-tertiary rounded small mb-0">{{ $raw ?? '' }}</pre>
            </details>
        </div>
    </div>
@endsection
