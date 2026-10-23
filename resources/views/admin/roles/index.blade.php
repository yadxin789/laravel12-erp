@extends('layouts.admin')

@section('title', '角色管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-user-shield"></i>角色管理</h2>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>新建角色
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>岗位名称</th>
                            <th>角色标识</th>
                            <th>所属部门</th>
                            <th class="d-none d-lg-table-cell">岗位职责</th>
                            <th class="text-end">权限数量</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            @php $meta = $catalog[$role->name] ?? null; @endphp
                            <tr>
                                <td class="fw-semibold">{{ \App\Support\RoleCatalog::label($role) }}</td>
                                <td class="font-monospace small text-muted">{{ $role->name }}</td>
                                <td>{{ $meta['department'] ?? '—' }}</td>
                                <td class="d-none d-lg-table-cell small text-muted">{{ $meta['desc'] ?? '—' }}</td>
                                <td class="text-end">{{ $role->permissions->count() }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen me-1"></i>编辑
                                    </a>
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
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
                                <td colspan="6" class="erp-empty">暂无角色</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $roles->links() }}
            </div>
        </div>
    </div>
@endsection
