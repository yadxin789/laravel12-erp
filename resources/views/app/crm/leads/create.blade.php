@extends('layouts.business')
@section('title', '新建线索')
@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">新建销售线索</h2>
    <form method="POST" action="{{ route('app.crm.leads.store') }}">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 text-sm">线索名称 *</label>
                <input type="text" name="name" required class="w-full border rounded px-3 py-2" placeholder="公司或项目名">
            </div>
            <div>
                <label class="block mb-1 text-sm">联系人</label>
                <input type="text" name="contact_person" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 text-sm">电话</label>
                <input type="text" name="phone" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 text-sm">邮箱</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 text-sm">公司</label>
                <input type="text" name="company" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 text-sm">来源 *</label>
                <select name="source" required class="w-full border rounded px-3 py-2">
                    <option value="manual">手动录入</option>
                    <option value="website">网站咨询</option>
                    <option value="exhibition">展会</option>
                    <option value="referral">客户推荐</option>
                    <option value="cold_call">陌拜/电话</option>
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
