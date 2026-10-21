@extends('layouts.business')
@section('title', '新建客户发票')
@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">新建客户发票</h2>
    <form method="POST" action="{{ route('app.finance.invoices.store') }}">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 text-sm">客户 *</label>
                <select name="customer_id" required class="w-full border rounded px-3 py-2">
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm">发票日期 *</label>
                <input type="date" name="invoice_date" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 text-sm">到期日期</label>
                <input type="date" name="due_date" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 text-sm">未税金额 *</label>
                <input type="number" step="0.01" name="subtotal" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 text-sm">税额</label>
                <input type="number" step="0.01" name="tax_amount" value="0" class="w-full border rounded px-3 py-2">
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
