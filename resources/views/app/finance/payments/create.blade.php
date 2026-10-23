@extends('layouts.business')

@section('title', '登记收付款')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-money-bill-transfer"></i>登记收付款</h2>
        <a href="{{ route('app.finance.payments') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('app.finance.payments.store') }}">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="type">类型 <span class="text-danger">*</span></label>
                                <select id="type" name="type" class="form-select" required>
                                    <option value="receipt" {{ old('type', 'receipt') === 'receipt' ? 'selected' : '' }}>收款（客户付款）</option>
                                    <option value="payment" {{ old('type') === 'payment' ? 'selected' : '' }}>付款（付供应商）</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="payment_date">日期 <span class="text-danger">*</span></label>
                                <input type="date" id="payment_date" name="payment_date" class="form-control"
                                       value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="customer_id">客户</label>
                                <select id="customer_id" name="customer_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="supplier_id">供应商</label>
                                <select id="supplier_id" name="supplier_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="customer_invoice_id">关联客户发票</label>
                                <select id="customer_invoice_id" name="customer_invoice_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($invoices as $inv)
                                        <option value="{{ $inv->id }}" {{ old('customer_invoice_id') == $inv->id ? 'selected' : '' }}>
                                            {{ $inv->invoice_no }} (¥{{ number_format(($inv->total_amount ?? 0) - ($inv->paid_amount ?? 0), 2) }} 未收)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="supplier_bill_id">关联供应商账单</label>
                                <select id="supplier_bill_id" name="supplier_bill_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($bills as $bill)
                                        <option value="{{ $bill->id }}" {{ old('supplier_bill_id') == $bill->id ? 'selected' : '' }}>
                                            {{ $bill->bill_no }} (¥{{ number_format(($bill->total_amount ?? 0) - ($bill->paid_amount ?? 0), 2) }} 未付)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="amount">金额 <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" id="amount" name="amount" class="form-control"
                                       value="{{ old('amount') }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="method">收付方式</label>
                                <select id="method" name="method" class="form-select">
                                    @php
                                        $methods = [
                                            'bank_transfer' => '银行转账',
                                            'cash' => '现金',
                                            'check' => '支票',
                                            'alipay' => '支付宝',
                                            'wechat' => '微信',
                                        ];
                                    @endphp
                                    @foreach($methods as $value => $label)
                                        <option value="{{ $value }}" {{ old('method', 'bank_transfer') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="reference_no">流水号 / 支票号</label>
                                <input type="text" id="reference_no" name="reference_no" class="form-control" value="{{ old('reference_no') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="remark">备注</label>
                                <textarea id="remark" name="remark" rows="3" class="form-control">{{ old('remark') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                            <a href="{{ route('app.finance.payments') }}" class="btn btn-light">取消</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
