@extends('layouts.business')

@section('title', '编辑销售订单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-regular fa-pen-to-square"></i>编辑销售订单 - {{ $order->order_no }}</h2>
        <a href="{{ route('app.sales.orders.show', $order) }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.sales.orders.update', $order) }}">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="customer_id">客户 <span class="text-danger">*</span></label>
                        <select id="customer_id" name="customer_id" class="form-select" required>
                            <option value="">请选择客户</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ $order->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="order_date">订单日期 <span class="text-danger">*</span></label>
                        <input type="date" id="order_date" name="order_date" class="form-control"
                               value="{{ $order->order_date->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="delivery_date">交货日期 <span class="text-danger">*</span></label>
                        <input type="date" id="delivery_date" name="delivery_date" class="form-control"
                               value="{{ $order->delivery_date->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">状态</label>
                        <div>
                            <span class="badge bg-light text-secondary border">{{ $order->status?->label() ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="form-label mb-0">订单明细</span>
                        <button type="button" onclick="addLine()" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-plus me-1"></i>添加明细
                        </button>
                    </div>

                    <div id="order-lines">
                        @foreach($order->lines as $index => $line)
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-12 col-md-4">
                                    <select name="lines[{{ $index }}][item_id]" class="form-select" required>
                                        <option value="">选择物料</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" {{ $line->item_id == $item->id ? 'selected' : '' }}>{{ $item->name }} - {{ $item->spec }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <input type="number" name="lines[{{ $index }}][quantity]" placeholder="数量" class="form-control"
                                           value="{{ $line->quantity }}" required>
                                </div>
                                <div class="col-6 col-md-2">
                                    <input type="number" name="lines[{{ $index }}][unit_price]" placeholder="单价" class="form-control"
                                           value="{{ $line->unit_price }}" required step="0.01">
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="text-muted small">小计: ¥{{ number_format($line->quantity * $line->unit_price, 2) }}</div>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <button type="button" onclick="removeLine(this)" class="btn btn-outline-danger">
                                        <i class="fa-regular fa-trash-can"></i>删除
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="alert alert-light border mb-4">
                    <span class="text-muted">订单总额：</span>
                    <span class="fs-5 fw-bold">¥{{ number_format($order->total_amount, 2) }}</span>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存修改</button>
                    <a href="{{ route('app.sales.orders.show', $order) }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let lineIndex = {{ $order->lines->count() }};
const items = @json($items);

function addLine() {
    const container = document.getElementById('order-lines');
    const options = items.map(item => `<option value="${item.id}">${item.name} - ${item.spec || ''}</option>`).join('');

    const html = `
        <div class="row g-2 align-items-center mb-2">
            <div class="col-12 col-md-4">
                <select name="lines[${lineIndex}][item_id]" class="form-select" required>
                    <option value="">选择物料</option>
                    ${options}
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="number" name="lines[${lineIndex}][quantity]" placeholder="数量" class="form-control" required>
            </div>
            <div class="col-6 col-md-2">
                <input type="number" name="lines[${lineIndex}][unit_price]" placeholder="单价" class="form-control" required step="0.01">
            </div>
            <div class="col-6 col-md-2">
                <div class="text-muted small"></div>
            </div>
            <div class="col-6 col-md-auto">
                <button type="button" onclick="removeLine(this)" class="btn btn-outline-danger">
                    <i class="fa-regular fa-trash-can"></i>删除
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    lineIndex++;
}

function removeLine(btn) {
    const container = document.getElementById('order-lines');
    if (container.children.length > 1) {
        btn.closest('.row').remove();
    } else {
        alert('至少需要一条明细行');
    }
}
</script>
@endpush
