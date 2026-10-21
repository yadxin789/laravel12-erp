@extends('layouts.business')
@section('title', '新建商机')
@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">新建商机</h2>
    <form method="POST" action="{{ route('app.crm.opportunities.store') }}">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block mb-1 text-sm">商机名称 *</label>
                <input type="text" name="name" required class="w-full border rounded px-3 py-2" placeholder="如：XX公司电阻采购项目">
            </div>
            <div>
                <label class="block mb-1 text-sm">关联线索</label>
                <select name="lead_id" class="w-full border rounded px-3 py-2">
                    <option value="">—</option>
                    @foreach($leads as $l)
                    <option value="{{ $l->id }}">{{ $l->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm">关联客户</label>
                <select name="customer_id" class="w-full border rounded px-3 py-2">
                    <option value="">—</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm">预计金额 *</label>
                <input type="number" step="0.01" name="expected_amount" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 text-sm">成交概率 (%)</label>
                <input type="number" name="probability" value="50" min="0" max="100" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 text-sm">预计成交日期</label>
                <input type="date" name="expected_close_date" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 text-sm">阶段 *</label>
                <select name="stage" required class="w-full border rounded px-3 py-2">
                    <option value="prospecting">初步接触</option>
                    <option value="qualification">需求确认</option>
                    <option value="proposal">方案报价</option>
                    <option value="negotiation">商务谈判</option>
                    <option value="closed_won">已成交</option>
                    <option value="closed_lost">已丢失</option>
                </select>
            </div>
            <div class="col-span-2">
                <label class="block mb-1 text-sm">备注</label>
                <textarea name="remark" class="w-full border rounded px-3 py-2" rows="3"></textarea>
            </div>
        </div>
        <button class="mt-4 bg-blue-600 text-white px-6 py-2 rounded">保存</button>
    </form>
</div>
@endsection
