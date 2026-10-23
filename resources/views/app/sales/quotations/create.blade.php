@extends('layouts.business')

@section('title', '新建报价单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-file-circle-plus"></i>新建报价单</h2>
        <a href="{{ route('app.sales.quotations.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.sales.quotations.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="customer_id">客户 <span class="text-danger">*</span></label>
                        <select id="customer_id" name="customer_id" class="form-select" required>
                            <option value="">请选择客户</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="quotation_date">报价日期 <span class="text-danger">*</span></label>
                        <input type="date" id="quotation_date" name="quotation_date" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="valid_until">有效期至 <span class="text-danger">*</span></label>
                        <input type="date" id="valid_until" name="valid_until" class="form-control" required>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="form-label mb-0">报价明细</span>
                        <button type="button" onclick="addLine()" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-plus me-1"></i>添加明细
                        </button>
                    </div>

                    <div id="quotation-lines">
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-12 col-md-4">
                                <select name="lines[0][item_id]" class="form-select" required>
                                    <option value="">选择物料</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->spec }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <input type="number" name="lines[0][quantity]" placeholder="数量" class="form-control" required>
                            </div>
                            <div class="col-6 col-md-2">
                                <input type="number" name="lines[0][unit_price]" placeholder="单价" class="form-control" required step="0.01">
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
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建报价单</button>
                    <a href="{{ route('app.sales.quotations.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let lineIndex = 1;
function addLine() {
    const container = document.getElementById('quotation-lines');
    const html = `
        <div class="row g-2 align-items-center mb-2">
            <div class="col-12 col-md-4">
                <select name="lines[${lineIndex}][item_id]" class="form-select" required>
                    <option value="">选择物料</option>
                    @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->spec }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="number" name="lines[${lineIndex}][quantity]" placeholder="数量" class="form-control" required>
            </div>
            <div class="col-6 col-md-2">
                <input type="number" name="lines[${lineIndex}][unit_price]" placeholder="单价" class="form-control" required step="0.01">
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
</script>
@endpush
