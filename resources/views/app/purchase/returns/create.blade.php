@extends('layouts.business')

@section('title', '新建采购退货单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-right-left"></i>新建采购退货单</h2>
        <a href="{{ route('app.purchase.returns.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.purchase.returns.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="purchase_order_id">采购订单 <span class="text-danger">*</span></label>
                        <select id="purchase_order_id" name="purchase_order_id" class="form-select" required>
                            <option value="">选择订单</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}">{{ $order->order_no }} - {{ $order->supplier?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="return_date">退货日期 <span class="text-danger">*</span></label>
                        <input type="date" id="return_date" name="return_date" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="warehouse_id">仓库 <span class="text-danger">*</span></label>
                        <select id="warehouse_id" name="warehouse_id" class="form-select" required>
                            <option value="">选择仓库</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="reason">退货原因 <span class="text-danger">*</span></label>
                    <textarea id="reason" name="reason" rows="3" class="form-control" required>{{ old('reason') }}</textarea>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="form-label mb-0">退货明细</span>
                        <button type="button" onclick="addLine()" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-plus me-1"></i>添加明细
                        </button>
                    </div>

                    <div id="return-lines">
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-12 col-md-6">
                                <select name="lines[0][purchase_order_line_id]" class="form-select" required>
                                    <option value="">选择订单行</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="number" name="lines[0][quantity]" placeholder="数量" class="form-control" required min="1">
                            </div>
                            <div class="col-6 col-md-auto">
                                <button type="button" onclick="removeLine(this)" class="btn btn-outline-danger">
                                    <i class="fa-regular fa-trash-can"></i>删除
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建退货单</button>
                    <a href="{{ route('app.purchase.returns.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let lineIndex = 1;
function addLine() {
    const container = document.getElementById('return-lines');
    const html = `
        <div class="row g-2 align-items-center mb-2">
            <div class="col-12 col-md-6">
                <select name="lines[${lineIndex}][purchase_order_line_id]" class="form-select" required>
                    <option value="">选择订单行</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <input type="number" name="lines[${lineIndex}][quantity]" placeholder="数量" class="form-control" required min="1">
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
    btn.closest('.row').remove();
}
</script>
@endpush
