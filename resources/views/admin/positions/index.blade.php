@extends('layouts.admin')

@section('title', '岗位管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-id-badge"></i>岗位管理</h2>
        <a href="{{ route('admin.positions.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>新建岗位
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.positions.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="department_id">按部门筛选</label>
                    <select id="department_id" name="department_id" class="form-select">
                        <option value="">全部部门</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>筛选</button>
                    <a href="{{ route('admin.positions.index') }}" class="btn btn-light">重置</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>编码</th>
                            <th>岗位名称</th>
                            <th>所属部门</th>
                            <th>职级</th>
                            <th>默认授予角色</th>
                            <th class="text-center">编制</th>
                            <th class="text-center">在职</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 140px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($positions as $pos)
                            <tr>
                                <td class="font-monospace small">{{ $pos->code }}</td>
                                <td class="fw-semibold">{{ $pos->name }}</td>
                                <td>{{ $pos->department?->name ?: '—' }}</td>
                                <td>{{ $pos->level ?: '—' }}</td>
                                <td>
                                    @if($pos->default_role)
                                        <span class="badge border bg-primary-subtle text-primary-emphasis border-primary-subtle">
                                            {{ $pos->default_role_label }}
                                        </span>
                                    @else
                                        <span class="text-muted small">不自动授权</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $pos->headcount }}</td>
                                <td class="text-center">{{ $pos->employee_count }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $pos->active ? 'bg-success-subtle text-success-emphasis border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle' }}">
                                        {{ $pos->active ? '启用' : '停用' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.positions.edit', $pos) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen me-1"></i>编辑
                                    </a>
                                    <form action="{{ route('admin.positions.destroy', $pos) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('确定删除岗位「{{ $pos->name }}」？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can me-1"></i>删除
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="erp-empty">暂无岗位，点击右上角「新建岗位」创建</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $positions->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
