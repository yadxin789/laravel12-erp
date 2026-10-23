@extends('layouts.admin')

@section('title', '权限管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-key"></i>权限管理</h2>
        <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>新建权限
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>权限名称</th>
                            <th>所属模块</th>
                            <th>权限标识</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                            <tr>
                                <td class="fw-semibold">{{ $permission->display ?: $permission->name }}</td>
                                <td>
                                    <span class="badge border bg-light text-secondary-emphasis border-secondary-subtle">
                                        {{ $groups[$permission->group] ?? ($permission->group ?: '未分组') }}
                                    </span>
                                </td>
                                <td class="font-monospace small text-muted">{{ $permission->name }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen me-1"></i>编辑
                                    </a>
                                    <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('确定删除？')">
                                            <i class="fa-regular fa-trash-can me-1"></i>删除
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="erp-empty">暂无权限</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
