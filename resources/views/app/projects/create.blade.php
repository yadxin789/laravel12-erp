@extends('layouts.business')

@section('title', '新建项目')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-diagram-project"></i>新建项目</h2>
        <a href="{{ route('app.projects.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.projects.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="name">项目名称 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required value="{{ old('name') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="status">状态</label>
                        <select id="status" name="status" class="form-select">
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('status', 'planning') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="budget">项目预算</label>
                        <input type="number" step="0.01" id="budget" name="budget" class="form-control"
                               value="{{ old('budget', 0) }}">
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="start_date">开始日期</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="end_date">结束日期</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="customer_id">关联客户</label>
                        <select id="customer_id" name="customer_id" class="form-select">
                            <option value="">（无）</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="manager_id">项目负责人</label>
                        <select id="manager_id" name="manager_id" class="form-select">
                            <option value="">（默认当前登录人）</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ old('manager_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="description">项目说明</label>
                        <textarea id="description" name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建</button>
                    <a href="{{ route('app.projects.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
