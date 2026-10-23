@extends('layouts.admin')

@section('title', '部门管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-sitemap"></i>部门管理</h2>
        <a href="{{ route('admin.departments.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>新建部门
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>编码</th>
                            <th>部门名称</th>
                            <th>简称</th>
                            <th>上级部门</th>
                            <th class="text-center">子部门</th>
                            <th class="text-center">排序</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 140px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $dept)
                            <tr>
                                <td class="font-monospace small">{{ $dept->code }}</td>
                                <td class="fw-semibold">{{ $dept->name }}</td>
                                <td>{{ $dept->short_name ?: '—' }}</td>
                                <td>{{ $dept->parent?->name ?: '—' }}</td>
                                <td class="text-center">{{ $dept->children_count }}</td>
                                <td class="text-center">{{ $dept->sort_order }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $dept->active ? 'bg-success-subtle text-success-emphasis border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle' }}">
                                        {{ $dept->active ? '启用' : '停用' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.departments.edit', $dept) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen me-1"></i>编辑
                                    </a>
                                    <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('确定删除部门「{{ $dept->name }}」？')">
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
                                <td colspan="8" class="erp-empty">暂无部门，点击右上角「新建部门」创建</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $departments->links() }}
            </div>
        </div>
    </div>
@endsection
