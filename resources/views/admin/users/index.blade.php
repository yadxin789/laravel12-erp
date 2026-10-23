@extends('layouts.admin')

@section('title', '用户管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-users"></i>用户管理</h2>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>新建用户
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>用户名</th>
                            <th>邮箱</th>
                            <th>角色</th>
                            <th>创建时间</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="fw-semibold">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->roles->isNotEmpty())
                                        <span class="badge border bg-light text-secondary-emphasis border-secondary-subtle">
                                            {{ \App\Support\RoleCatalog::label($user->roles->first()) }}
                                        </span>
                                        <span class="font-monospace small text-muted ms-1">{{ $user->roles->first()->name }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $user->created_at?->format('Y-m-d') ?? '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen me-1"></i>编辑
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
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
                                <td colspan="5" class="erp-empty">暂无用户</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
