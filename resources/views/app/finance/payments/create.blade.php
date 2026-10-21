@extends('layouts.business')
@section('title', '登记收付款')
@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">登记收付款</h2>
    <form method="POST" action="{{ route('app.finance.payments.store') }}">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 text-sm">类型 *</label>
                <select name="type" id="payment-type" required class="w-full border rounded px-3 py-2">
                    <option value="receipt">收款（客户付款）</option>
                    <option value="payment">付款（付供应商）</option>
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm">日期 *</label>
                <input type="date" name="payment_date" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 text-sm">客户</label>
                <select name="customer_id" class="w-full border rounded px-3 py-2">
                    <option value="">—</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm">供应商</label>
                <select name="supplier_id" class="w-full border rounded px-3 py-2">
                    <option value="">—</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm">关联客户发票</label>
                <select name="customer_invoice_id" class="w-full border rounded px-3 py-2">
                    <option value="">—</option>
                    @foreach($invoices as $inv)
                    <option value="{{ $inv->id }}">{{ $inv->invoice_no }} (¥{{ number_format($inv->total_amount - $inv->paid_amount, 2) }} 未收)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm">关联供应商账单</label>
                <select name="supplier_bill_id" class="w-full border rounded px-3 py-2">
                    <option value="">—</option>
                    @foreach($bills as $bill)
                    <option value="{{ $bill->id }}">{{ $bill->bill_no }} (¥{{ number_format($bill->total_amount - $bill->paid_amount, 2) }} 未付)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm">金额 *</label>
                <input type="number" step="0.01" name="amount" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 text-sm">收款方式</label>
                <select name="method" class="w-full border rounded px-3 py-2">
                    <option value="bank_transfer">银行转账</option>
                    <option value="cash">现金</option>
                    <option value="check">支票</option>
                    <option value="alipay">支付宝</option>
                    <option value="wechat">微信</option>
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm">流水号/支票号</label>
                <input type="text" name="reference_no" class="w-full border rounded px-3 py-2">
            </div>
            <div class="col-span-2">
                <label class="block mb-1 text-sm">备注</label>
                <textarea name="remark" class="w-full border rounded px-3 py-2"></textarea>
            </div>
        </div>
        <button class="mt-4 bg-blue-600 text-white px-6 py-2 rounded">保存</button>
    </form>
</div>
@endsection
