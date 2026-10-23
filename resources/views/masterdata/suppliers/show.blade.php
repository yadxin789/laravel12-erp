@extends('layouts.app')

@section('title', '供应商详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-truck-field"></i>供应商详情</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-primary">
                <i class="fa-regular fa-pen-to-square me-1"></i>编辑
            </a>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="text-muted small">编码</div>
                    <div class="fw-semibold">{{ $supplier->code }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">名称</div>
                    <div class="fw-semibold">{{ $supplier->name }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">联系人</div>
                    <div class="fw-semibold">{{ $supplier->contact_person ?? '-' }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">电话</div>
                    <div class="fw-semibold">{{ $supplier->phone ?? '-' }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">邮箱</div>
                    <div class="fw-semibold">{{ $supplier->email ?? '-' }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">地址</div>
                    <div class="fw-semibold">{{ $supplier->address ?? '-' }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">创建时间</div>
                    <div class="fw-semibold">{{ $supplier->created_at }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">更新时间</div>
                    <div class="fw-semibold">{{ $supplier->updated_at }}</div>
                </div>
            </div>

            <div class="mt-4">
                <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST"
                      onsubmit="return confirm('确定删除供应商【{{ $supplier->name }}】吗？')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fa-regular fa-trash-can me-1"></i>删除
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
