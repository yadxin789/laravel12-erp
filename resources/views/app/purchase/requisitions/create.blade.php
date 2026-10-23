@extends('layouts.business')

@section('title', '新建请购单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-list"></i>新建请购单</h2>
        <a href="{{ route('app.purchase.requisitions.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.purchase.requisitions.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="requisition_date">请购日期 <span class="text-danger">*</span></label>
                        <input type="date" id="requisition_date" name="requisition_date"
                               value="{{ old('requisition_date', date('Y-m-d')) }}" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="warehouse_id">仓库 <span class="text-danger">*</span></label>
                        <select id="warehouse_id" name="warehouse_id" class="form-select" required>
                            <option value="">选择仓库</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="form-label mb-0">请购明细</span>
                        <button type="button" onclick="addLine()" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-plus me-1"></i>添加物料
                        </button>
                    </div>

                    <div id="requisition-lines">
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-12 col-md-5">
                                <select name="lines[0][item_id]" class="form-select" required>
                                    <option value="">选择物料</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->spec }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <input type="number" name="lines[0][quantity]" placeholder="数量" class="form-control" required min="1">
                            </div>
                            <div class="col-6 col-md-2">
                                <input type="number" name="lines[0][unit_price]" placeholder="预估单价" class="form-control" required step="0.01">
                            </div>
                            <div class="col-12 col-md-auto">
                                <button type="button" onclick="removeLine(this)" class="btn btn-outline-danger">
                                    <i class="fa-regular fa-trash-can"></i>删除
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="remark">备注</label>
                    <textarea id="remark" name="remark" rows="2" class="form-control">{{ old('remark') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建请购单</button>
                    <a href="{{ route('app.purchase.requisitions.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let lineIndex = 1;
function addLine() {
    const container = document.getElementById('requisition-lines');
    const html = `
        <div class="row g-2 align-items-center mb-2">
            <div class="col-12 col-md-5">
                <select name="lines[${lineIndex}][item_id]" class="form-select" required>
                    <option value="">选择物料</option>
                    @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->spec }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="number" name="lines[${lineIndex}][quantity]" placeholder="数量" class="form-control" required min="1">
            </div>
            <div class="col-6 col-md-2">
                <input type="number" name="lines[${lineIndex}][unit_price]" placeholder="预估单价" class="form-control" required step="0.01">
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
