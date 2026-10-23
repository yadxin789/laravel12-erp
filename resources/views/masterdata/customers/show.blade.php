@extends('layouts.app')

@section('title', '客户详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-building-user"></i>客户详情</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary">
                <i class="fa-regular fa-pen-to-square me-1"></i>编辑
            </a>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="text-muted small">编码</div>
                    <div class="fw-semibold">{{ $customer->code }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">名称</div>
                    <div class="fw-semibold">{{ $customer->name }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">联系人</div>
                    <div class="fw-semibold">{{ $customer->contact_person ?? '-' }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">电话</div>
                    <div class="fw-semibold">{{ $customer->phone ?? '-' }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">邮箱</div>
                    <div class="fw-semibold">{{ $customer->email ?? '-' }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">地址</div>
                    <div class="fw-semibold">{{ $customer->address ?? '-' }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">创建时间</div>
                    <div class="fw-semibold">{{ $customer->created_at }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="text-muted small">更新时间</div>
                    <div class="fw-semibold">{{ $customer->updated_at }}</div>
                </div>
            </div>

            <div class="mt-4">
                <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST"
                      onsubmit="return confirm('确定删除客户【{{ $customer->name }}】吗？')">
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
