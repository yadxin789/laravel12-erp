@extends('layouts.app')

@section('title', '物料管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-boxes-stacked"></i>物料管理</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.items.barcode-labels') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-barcode me-1"></i>打印条码标签
            </a>
            <a href="{{ route('admin.items.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i>创建物料
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-4">
                <div class="col-12 col-md-3">
                    <label class="form-label" for="code">物料编码</label>
                    <input type="text" id="code" name="code" value="{{ request('code') }}" class="form-control" placeholder="物料编码">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="name">物料名称</label>
                    <input type="text" id="name" name="name" value="{{ request('name') }}" class="form-control" placeholder="物料名称">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="barcode">条码</label>
                    <input type="text" id="barcode" name="barcode" value="{{ request('barcode') }}" class="form-control" placeholder="条码">
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass me-1"></i>搜索</button>
                    <a href="{{ route('admin.items.index') }}" class="btn btn-light">重置</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 70px;">图片</th>
                            <th>编码</th>
                            <th>条码</th>
                            <th>名称</th>
                            <th>类别</th>
                            <th>单位</th>
                            <th class="text-end">库存</th>
                            <th class="text-end" style="width: 260px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>
                                    @if($item->image_url)
                                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="rounded" width="40" height="40" style="object-fit: cover;">
                                    @else
                                        <span class="badge bg-light text-secondary">无图</span>
                                    @endif
                                </td>
                                <td class="font-monospace small">{{ $item->code }}</td>
                                <td class="font-monospace small text-muted">{{ $item->barcode ?? '—' }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->category?->name }}</td>
                                <td>{{ $item->unit?->name }}</td>
                                <td class="text-end">{{ $item->total_stock }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.items.show', $item) }}" class="btn btn-outline-primary">
                                            <i class="fa-regular fa-eye"></i>查看
                                        </a>
                                        <a href="{{ route('admin.items.edit', $item) }}" class="btn btn-outline-secondary">
                                            <i class="fa-regular fa-pen-to-square"></i>编辑
                                        </a>
                                        <a href="{{ route('admin.items.barcode-label', $item) }}" class="btn btn-outline-secondary">
                                            <i class="fa-solid fa-barcode"></i>条码
                                        </a>
                                        <form action="{{ route('admin.items.destroy', $item) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('确定删除物料【{{ $item->name }}】吗？此操作不可恢复。')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="fa-regular fa-trash-can"></i>删除
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="erp-empty">暂无物料数据</td>
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
