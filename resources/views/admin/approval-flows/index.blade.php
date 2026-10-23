@extends('layouts.admin')

@section('title', '审批流配置')

@php
    $roleOptions = $roles->map(fn ($r) => [
        'value' => $r->name,
        'label' => \App\Support\RoleCatalog::label($r),
    ])->values();
@endphp

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-check-to-slot"></i>审批流配置</h2>
    </div>

    @forelse($flows as $flow)
        <div class="card mb-3">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span>
                    {{ $flow->name }}
                    <span class="text-muted small">[{{ $flow->document_type }}]</span>
                </span>
                <span class="badge border {{ $flow->enabled ? 'bg-success-subtle text-success-emphasis border-success-subtle' : 'bg-light text-secondary border' }}">
                    {{ $flow->enabled ? '启用中' : '已停用' }}
                </span>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @forelse($flow->steps->sortBy('step_order') as $step)
                        @php
                            $approver = $step->approver_type === 'role'
                                ? \App\Support\RoleCatalog::label($step->approver_value)
                                : (\App\Models\User::find($step->approver_value)?->name ?? $step->approver_value);
                        @endphp
                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                            {{ $step->step_order }}. {{ $step->name }}（{{ $approver }}）
                        </span>
                        @if(!$loop->last)
                            <i class="fa-solid fa-arrow-right text-muted small"></i>
                        @endif
                    @empty
                        <span class="text-muted small">暂无审批步骤</span>
                    @endforelse
                </div>
            </div>
        </div>
    @empty
        <div class="card mb-4">
            <div class="card-body">
                <p class="erp-empty mb-0">暂无审批流配置</p>
            </div>
        </div>
    @endforelse

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-plus me-2"></i>新建审批流</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.approval-flows.store') }}">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="flow_name">流程名称 <span class="text-danger">*</span></label>
                        <input type="text" id="flow_name" name="name" class="form-control" required
                               placeholder="如：销售订单审批流" value="{{ old('name') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="document_type">单据类型 <span class="text-danger">*</span></label>
                        <select id="document_type" name="document_type" class="form-select" required>
                            <option value="sales_order">销售订单</option>
                            <option value="purchase_order">采购订单</option>
                            <option value="requisition">请购单</option>
                            <option value="quality">质检单</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">审批步骤</label>
                    <div id="steps-container" class="vstack gap-2"></div>
                    <button type="button" id="add-step" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="fa-solid fa-plus me-1"></i>添加步骤
                    </button>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存审批流</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const roleOptions = @json($roleOptions);
let stepIdx = 0;

function approverValueHtml(idx, type) {
    if (type === 'user') {
        return `<input type="text" name="steps[${idx}][approver_value]" placeholder="用户ID / 邮箱" required class="form-control form-control-sm">`;
    }

    const opts = roleOptions.map((o) => `<option value="${o.value}">${o.label}（${o.value}）</option>`).join('');

    return `<select name="steps[${idx}][approver_value]" class="form-select form-select-sm" required>${opts}</select>`;
}

function toggleApprover(selectEl) {
    const row = selectEl.closest('.step-row');
    const idx = row.dataset.idx;
    const wrap = row.querySelector('.approver-value-wrap');
    wrap.innerHTML = approverValueHtml(idx, selectEl.value);
}

function addStep() {
    const container = document.getElementById('steps-container');
    const row = document.createElement('div');
    row.className = 'row g-2 align-items-end step-row';
    row.dataset.idx = stepIdx;
    row.innerHTML = `
        <div class="col-12 col-md-4">
            <input type="text" name="steps[${stepIdx}][name]" placeholder="步骤名称" required class="form-control form-control-sm">
        </div>
        <div class="col-6 col-md-3">
            <select name="steps[${stepIdx}][approver_type]" class="form-select form-select-sm" onchange="toggleApprover(this)">
                <option value="role">按岗位角色</option>
                <option value="user">按指定人</option>
            </select>
        </div>
        <div class="col-6 col-md-3 approver-value-wrap">${approverValueHtml(stepIdx, 'role')}</div>
        <div class="col-12 col-md-2">
            <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="this.closest('.step-row').remove()">
                <i class="fa-solid fa-trash me-1"></i>删除
            </button>
        </div>
    `;
    container.appendChild(row);
    stepIdx++;
}

document.getElementById('add-step').addEventListener('click', addStep);
addStep();
</script>
@endpush
