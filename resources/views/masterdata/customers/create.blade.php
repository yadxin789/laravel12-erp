@extends('layouts.app')

@section('title', '创建客户')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-plus"></i>创建客户</h2>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.customers.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="code">客户编码 <span class="text-danger">*</span></label>
                        <input type="text" id="code" name="code" required value="{{ old('code') }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="name">客户名称 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" required value="{{ old('name') }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="contact_person">联系人</label>
                        <input type="text" id="contact_person" name="contact_person" value="{{ old('contact_person') }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="phone">电话</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="email">邮箱</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="address">地址</label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}" class="form-control">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
