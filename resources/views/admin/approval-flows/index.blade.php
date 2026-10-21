@extends('layouts.admin')
@section('title', '审批流配置')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">审批流配置</h2>

    @foreach($flows as $flow)
    <div class="border rounded p-4 mb-4">
        <div class="flex justify-between items-center mb-2">
            <h3 class="font-bold">{{ $flow->name }} <span class="text-sm text-gray-500">[{{ $flow->document_type }}]</span></h3>
            <span class="text-xs {{ $flow->enabled ? 'text-green-600' : 'text-gray-400' }}">{{ $flow->enabled ? '启用中' : '已停用' }}</span>
        </div>
        <div class="flex items-center gap-2 text-sm">
            @foreach($flow->steps->sortBy('step_order') as $step)
                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">{{ $step->step_order }}. {{ $step->name }} ({{ $step->approver_value }})</span>
                @if(!$loop->last) <span class="text-gray-400">→</span> @endif
            @endforeach
        </div>
    </div>
    @endforeach

    {{-- 新建审批流 --}}
    <div class="border-t pt-6 mt-6">
        <h3 class="font-bold mb-3">新建审批流</h3>
        <form method="POST" action="{{ route('admin.approval-flows.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-sm">流程名称</label>
                    <input type="text" name="name" required class="w-full border rounded px-3 py-2" placeholder="如：销售订单审批流">
                </div>
                <div>
                    <label class="text-sm">单据类型</label>
                    <select name="document_type" required class="w-full border rounded px-3 py-2">
                        <option value="sales_order">销售订单</option>
                        <option value="purchase_order">采购订单</option>
                        <option value="requisition">请购单</option>
                        <option value="quality">质检单</option>
                    </select>
                </div>
            </div>
            <div id="steps-container">
                <div class="grid grid-cols-4 gap-2 mb-2">
                    <input type="text" name="steps[0][name]" placeholder="步骤名称" required class="border rounded px-2 py-1 text-sm">
                    <select name="steps[0][approver_type]" class="border rounded px-2 py-1 text-sm">
                        <option value="role">按角色</option>
                        <option value="user">按指定人</option>
                    </select>
                    <input type="text" name="steps[0][approver_value]" placeholder="角色名/用户ID" required class="border rounded px-2 py-1 text-sm">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600 text-sm">删除</button>
                </div>
            </div>
            <button type="button" onclick="addStep()" class="text-blue-600 text-sm mb-3">+ 添加步骤</button>
            <br>
            <button class="bg-blue-600 text-white px-6 py-2 rounded">保存审批流</button>
        </form>
    </div>
</div>
<script>
let stepIdx = 1;
function addStep() {
    const c = document.getElementById('steps-container');
    const div = document.createElement('div');
    div.className = 'grid grid-cols-4 gap-2 mb-2';
    div.innerHTML = `
        <input type="text" name="steps[${stepIdx}][name]" placeholder="步骤名称" required class="border rounded px-2 py-1 text-sm">
        <select name="steps[${stepIdx}][approver_type]" class="border rounded px-2 py-1 text-sm">
            <option value="role">按角色</option>
            <option value="user">按指定人</option>
        </select>
        <input type="text" name="steps[${stepIdx}][approver_value]" placeholder="角色名/用户ID" required class="border rounded px-2 py-1 text-sm">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-600 text-sm">删除</button>
    `;
    c.appendChild(div);
    stepIdx++;
}
</script>
@endsection
