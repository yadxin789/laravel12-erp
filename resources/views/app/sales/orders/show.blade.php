@extends('layouts.business')

@section('title', '销售订单详情')

@section('content')
    @php
        use App\Enums\SalesOrderStatus;
        $status = $order->status;
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-file-invoice-dollar"></i>销售订单: {{ $order->order_no }}</h2>
        <div class="d-flex flex-wrap gap-2">
            @if($status === SalesOrderStatus::Draft)
                <a href="{{ route('app.sales.orders.edit', $order) }}" class="btn btn-outline-secondary">
                    <i class="fa-regular fa-pen-to-square me-1"></i>编辑
                </a>
                <form action="{{ route('app.sales.orders.submit', $order) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>提交审核</button>
                </form>
            @elseif($status === SalesOrderStatus::Submitted)
                <form action="{{ route('app.sales.orders.approve', $order) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i>审核通过</button>
                </form>
            @elseif($status === SalesOrderStatus::Approved)
                <form action="{{ route('app.sales.orders.close', $order) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary"><i class="fa-solid fa-lock me-1"></i>关闭订单</button>
                </form>
            @endif

            @if($status !== SalesOrderStatus::Cancelled && $status !== SalesOrderStatus::Closed)
                <form action="{{ route('app.sales.orders.cancel', $order) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('确定取消该订单吗？')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger"><i class="fa-solid fa-ban me-1"></i>取消订单</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-muted small">客户</div>
                    <div class="fw-semibold">{{ $order->customer?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">订单日期</div>
                    <div class="fw-semibold">{{ $order->order_date }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">交货日期</div>
                    <div class="fw-semibold">{{ $order->delivery_date }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">状态</div>
                    <div><span class="badge bg-light text-secondary border">{{ $status?->label() ?? '-' }}</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-list me-2"></i>订单明细</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>物料</th>
                            <th>规格</th>
                            <th class="text-end">数量</th>
                            <th class="text-end">单价</th>
                            <th class="text-end">金额</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->lines as $line)
                            <tr>
                                <td>{{ $line->item?->name }}</td>
                                <td>{{ $line->item?->spec }}</td>
                                <td class="text-end">{{ $line->quantity }}</td>
                                <td class="text-end">{{ $line->unit_price }}</td>
                                <td class="text-end">{{ $line->amount }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="erp-empty">暂无订单明细</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- AI 审批建议 --}}
    @if($status === SalesOrderStatus::Submitted)
        <div class="card border-primary-subtle mt-4">
            <div class="card-header bg-primary-subtle d-flex justify-content-between align-items-center">
                <span class="fw-bold text-primary-emphasis"><i class="fa-solid fa-robot me-2"></i>AI 审批建议</span>
                <button type="button" onclick="getAiSuggestion()" class="btn btn-sm btn-outline-primary">点击获取建议</button>
            </div>
            <div class="card-body">
                <div id="ai-suggestion" class="small text-muted">
                    点击按钮，AI 将分析订单数据、客户信用、库存等给出审批建议
                </div>
            </div>
        </div>
    @endif

    @include('components.attachment-section', ['model' => $order])
@endsection

@push('scripts')
<script>
async function getAiSuggestion() {
    const box = document.getElementById('ai-suggestion');
    box.innerHTML = '<span class="text-primary">AI 分析中...</span>';
    try {
        const resp = await fetch('{{ route('app.ai.approval-suggestion') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ order_type: 'sales', order_id: {{ $order->id }} })
        });
        const data = await resp.json();
        box.innerHTML = '<pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;">' + data.suggestion + '</pre>';
    } catch (e) {
        box.innerHTML = '<span class="text-danger">获取建议失败</span>';
    }
}
</script>
@endpush
