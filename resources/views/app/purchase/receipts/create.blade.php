@extends('layouts.business')

@section('title', '新建收货单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-boxes-packing"></i>新建收货单</h2>
        <a href="{{ route('app.purchase.receipts.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.purchase.receipts.store') }}">
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
                        <label class="form-label" for="receipt_date">收货日期 <span class="text-danger">*</span></label>
                        <input type="date" id="receipt_date" name="receipt_date" class="form-control" required>
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

                {{-- 扫码录入区 --}}
                <div class="alert alert-warning border-warning-subtle mb-4">
                    <label class="form-label fw-semibold" for="scan-input">
                        <i class="fa-solid fa-barcode me-1"></i>扫码查找物料（扫码枪/手动输入条码后回车）
                    </label>
                    <div class="input-group">
                        <input type="text" id="scan-input" class="form-control font-monospace" placeholder="扫描条码或物料编码..." autofocus>
                        <button type="button" id="scan-btn" class="btn btn-warning">查找</button>
                    </div>
                    <div id="scan-result" class="small mt-2"></div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="form-label mb-0">收货明细</span>
                        <button type="button" onclick="addLine()" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-plus me-1"></i>添加明细
                        </button>
                    </div>

                    <div id="receipt-lines">
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-12 col-md-4">
                                <select name="lines[0][purchase_order_line_id]" class="form-select" required>
                                    <option value="">选择订单行</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <input type="number" name="lines[0][quantity]" placeholder="数量" class="form-control" required min="1">
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="text" name="lines[0][batch_number]" placeholder="批次号" class="form-control">
                            </div>
                            <div class="col-12 col-md-auto">
                                <button type="button" onclick="removeLine(this)" class="btn btn-outline-danger">
                                    <i class="fa-regular fa-trash-can"></i>删除
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建收货单</button>
                    <a href="{{ route('app.purchase.receipts.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let lineIndex = 1;
function addLine() {
    const container = document.getElementById('receipt-lines');
    const html = `
        <div class="row g-2 align-items-center mb-2">
            <div class="col-12 col-md-4">
                <select name="lines[${lineIndex}][purchase_order_line_id]" class="form-select" required>
                    <option value="">选择订单行</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="number" name="lines[${lineIndex}][quantity]" placeholder="数量" class="form-control" required min="1">
            </div>
            <div class="col-6 col-md-3">
                <input type="text" name="lines[${lineIndex}][batch_number]" placeholder="批次号" class="form-control">
            </div>
            <div class="col-12 col-md-auto">
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

// 扫码查找物料
const scanInput = document.getElementById('scan-input');
const scanBtn = document.getElementById('scan-btn');
const scanResult = document.getElementById('scan-result');

async function doScan() {
    const barcode = scanInput.value.trim();
    if (!barcode) return;

    scanResult.innerHTML = '<span class="text-primary">查找中...</span>';

    try {
        const resp = await fetch(`/api/items/by-barcode/${encodeURIComponent(barcode)}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await resp.json();

        if (data.error) {
            scanResult.innerHTML = `<span class="text-danger">未找到物料: ${data.error}</span>`;
        } else {
            scanResult.innerHTML = `
                <div class="alert alert-success border-success-subtle py-2 px-3 mb-0">
                    <b>${data.name}</b> (${data.code})<br>
                    规格: ${data.spec || '—'} | 类别: ${data.category || '—'} | 单位: ${data.unit || '—'}
                </div>
            `;
            const firstQty = document.querySelector('input[name$="[quantity]"]');
            if (firstQty) firstQty.focus();
        }
    } catch (e) {
        scanResult.innerHTML = '<span class="text-danger">查询失败</span>';
    }
    scanInput.value = '';
}

scanBtn.addEventListener('click', doScan);
scanInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); doScan(); }
});
</script>
@endpush
