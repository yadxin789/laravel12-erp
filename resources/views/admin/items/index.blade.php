@extends('layouts.admin')

@section('title', '物料管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-boxes-stacked"></i>物料管理</h2>
        @can('create items')
        <a href="{{ route('admin.items.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建物料
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>编码</th>
                            <th>名称</th>
                            <th>规格</th>
                            <th>单位</th>
                            <th>类型</th>
                            <th style="width: 100px;">规格书</th>
                            <th class="text-end" style="width: 160px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td class="font-monospace small">{{ $item->code }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->spec ?? '-' }}</td>
                                <td>{{ $item->unit->name ?? '-' }}</td>
                                <td>{{ $item->item_type }}</td>
                                <td>
                                    @if($item->hasSpecSheet())
                                        <a href="{{ route('admin.items.spec-sheet', $item) }}" class="badge bg-success-subtle text-success-emphasis border border-success-subtle text-decoration-none">已上传</a>
                                    @else
                                        <a href="{{ route('admin.items.spec-sheet', $item) }}" class="badge bg-light text-secondary border text-decoration-none">未上传</a>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.items.show', $item) }}" class="btn btn-outline-primary">
                                            <i class="fa-regular fa-eye"></i>查看
                                        </a>
                                        @can('edit items', $item)
                                        <a href="{{ route('admin.items.edit', $item) }}" class="btn btn-outline-secondary">
                                            <i class="fa-regular fa-pen-to-square"></i>编辑
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">暂无物料数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $items->links() }}
            </div>
        </div>
    </div>
@endsection
