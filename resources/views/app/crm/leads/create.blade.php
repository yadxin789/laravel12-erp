@extends('layouts.business')

@section('title', '新建线索')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-user-plus"></i>新建销售线索</h2>
        <a href="{{ route('app.crm.leads') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('app.crm.leads.store') }}">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="name">线索名称 <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" required
                                       placeholder="公司或项目名" value="{{ old('name') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="contact_person">联系人</label>
                                <input type="text" id="contact_person" name="contact_person" class="form-control" value="{{ old('contact_person') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="phone">电话</label>
                                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="email">邮箱</label>
                                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="company">公司</label>
                                <input type="text" id="company" name="company" class="form-control" value="{{ old('company') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="source">来源 <span class="text-danger">*</span></label>
                                <select id="source" name="source" class="form-select" required>
                                    @php
                                        $sources = [
                                            'manual' => '手动录入',
                                            'website' => '网站咨询',
                                            'exhibition' => '展会',
                                            'referral' => '客户推荐',
                                            'cold_call' => '陌拜/电话',
                                        ];
                                    @endphp
                                    @foreach($sources as $value => $label)
                                        <option value="{{ $value }}" {{ old('source', 'manual') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="remark">备注</label>
                                <textarea id="remark" name="remark" rows="3" class="form-control">{{ old('remark') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                            <a href="{{ route('app.crm.leads') }}" class="btn btn-light">取消</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
