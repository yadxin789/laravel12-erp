@extends('layouts.business')

@section('title', '新建供应商账单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-money-bill-wave"></i>新建供应商账单</h2>
        <a href="{{ route('app.finance.bills') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('app.finance.bills.store') }}">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="supplier_id">供应商 <span class="text-danger">*</span></label>
                                <select id="supplier_id" name="supplier_id" class="form-select" required>
                                    <option value="">选择供应商</option>
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="bill_date">账单日期 <span class="text-danger">*</span></label>
                                <input type="date" id="bill_date" name="bill_date" class="form-control"
                                       value="{{ old('bill_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="due_date">到期日期</label>
                                <input type="date" id="due_date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label" for="subtotal">未税金额 <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" id="subtotal" name="subtotal" class="form-control"
                                       value="{{ old('subtotal') }}" required>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label" for="tax_amount">税额</label>
                                <input type="number" step="0.01" min="0" id="tax_amount" name="tax_amount" class="form-control"
                                       value="{{ old('tax_amount', 0) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="remark">备注</label>
                                <textarea id="remark" name="remark" rows="3" class="form-control">{{ old('remark') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                            <a href="{{ route('app.finance.bills') }}" class="btn btn-light">取消</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
