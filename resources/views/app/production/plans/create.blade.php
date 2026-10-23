@extends('layouts.business')

@section('title', '新建生产计划')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-calendar-plus"></i>新建生产计划</h2>
        <a href="{{ route('app.production.plans.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.production.plans.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="plan_date">计划日期 <span class="text-danger">*</span></label>
                        <input type="date" id="plan_date" name="plan_date" class="form-control" value="{{ old('plan_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="form-label" for="remark">备注</label>
                        <input type="text" id="remark" name="remark" class="form-control" value="{{ old('remark') }}" placeholder="可选备注">
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="form-label mb-0">计划明细</span>
                        <button type="button" onclick="addLine()" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-plus me-1"></i>添加明细
                        </button>
                    </div>

                    <div id="plan-lines">
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-12 col-md-3">
                                <select name="lines[0][item_id]" class="form-select" required>
                                    <option value="">选择物料</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->spec }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-1">
                                <input type="number" name="lines[0][quantity]" placeholder="数量" class="form-control" required step="0.0001">
                            </div>
                            <div class="col-6 col-md-2">
                                <input type="date" name="lines[0][plan_date]" class="form-control">
                            </div>
                            <div class="col-12 col-md-2">
                                <select name="lines[0][work_center_id]" class="form-select">
                                    <option value="">选择工作中心</option>
                                    @foreach($workCenters as $workCenter)
                                        <option value="{{ $workCenter->id }}">{{ $workCenter->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <select name="lines[0][sales_order_id]" class="form-select">
                                    <option value="">选择销售订单</option>
                                    @foreach($salesOrders as $salesOrder)
                                        <option value="{{ $salesOrder->id }}">{{ $salesOrder->order_no }} - {{ $salesOrder->customer?->name }}</option>
                                    @endforeach
                                </select>
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
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                    <a href="{{ route('app.production.plans.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let lineIndex = 1;
const items = @json($items);
const workCenters = @json($workCenters);
const salesOrders = @json($salesOrders);

function addLine() {
    const container = document.getElementById('plan-lines');

    const itemOptions = '<option value="">选择物料</option>' + items.map(item => `<option value="${item.id}">${item.name} - ${item.spec || ''}</option>`).join('');
    const workCenterOptions = '<option value="">选择工作中心</option>' + workCenters.map(wc => `<option value="${wc.id}">${wc.name}</option>`).join('');
    const salesOrderOptions = '<option value="">选择销售订单</option>' + salesOrders.map(so => `<option value="${so.id}">${so.order_no} - ${so.customer?.name || ''}</option>`).join('');

    const html = `
        <div class="row g-2 align-items-center mb-2">
            <div class="col-12 col-md-3">
                <select name="lines[${lineIndex}][item_id]" class="form-select" required>
                    ${itemOptions}
                </select>
            </div>
            <div class="col-6 col-md-1">
                <input type="number" name="lines[${lineIndex}][quantity]" placeholder="数量" class="form-control" required step="0.0001">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="lines[${lineIndex}][plan_date]" class="form-control">
            </div>
            <div class="col-12 col-md-2">
                <select name="lines[${lineIndex}][work_center_id]" class="form-select">
                    ${workCenterOptions}
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="lines[${lineIndex}][sales_order_id]" class="form-select">
                    ${salesOrderOptions}
                </select>
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
    const container = document.getElementById('plan-lines');
    // 至少保留一行明细
    if (container.children.length > 1) {
        btn.closest('.row').remove();
    } else {
        alert('至少需要一条明细行');
    }
}
</script>
@endpush
