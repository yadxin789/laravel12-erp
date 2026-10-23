@extends('layouts.business')

@section('title', '新建客户发票')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-file-invoice-dollar"></i>新建客户发票</h2>
        <a href="{{ route('app.finance.invoices') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('app.finance.invoices.store') }}">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="customer_id">客户 <span class="text-danger">*</span></label>
                                <select id="customer_id" name="customer_id" class="form-select" required>
                                    <option value="">选择客户</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="invoice_date">开票日期 <span class="text-danger">*</span></label>
                                <input type="date" id="invoice_date" name="invoice_date" class="form-control"
                                       value="{{ old('invoice_date', date('Y-m-d')) }}" required>
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
                            <a href="{{ route('app.finance.invoices') }}" class="btn btn-light">取消</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
