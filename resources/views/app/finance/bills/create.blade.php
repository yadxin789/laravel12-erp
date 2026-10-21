@extends('layouts.business')
@section('title', '新建供应商账单')
@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">新建供应商账单</h2>
    <form method="POST" action="{{ route('app.finance.bills.store') }}">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 text-sm">供应商 *</label>
                <select name="supplier_id" required class="w-full border rounded px-3 py-2">
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm">账单日期 *</label>
                <input type="date" name="bill_date" required class="w-full border rounded px-3 py-2">
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
