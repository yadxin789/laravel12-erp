@extends('layouts.business')

@section('title', '新建招聘需求')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-bullhorn"></i>新建招聘需求</h2>
        <a href="{{ route('app.hr.recruitments.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.hr.recruitments.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="req_no">需求编号</label>
                        <input type="text" id="req_no" name="req_no" class="form-control" value="{{ old('req_no') }}"
                               placeholder="留空自动生成 REC0001">
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="form-label" for="title">需求名称 <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" required
                               placeholder="如：装配工 3 名（业务扩产）" value="{{ old('title') }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="position_id">招聘岗位</label>
                        <select id="position_id" name="position_id" class="form-select">
                            <option value="">（未指定）</option>
                            @foreach($positions as $p)
                                <option value="{{ $p->id }}" {{ old('position_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="department_id">需求部门</label>
                        <select id="department_id" name="department_id" class="form-select">
                            <option value="">（未指定）</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="headcount">需求人数 <span class="text-danger">*</span></label>
                        <input type="number" id="headcount" name="headcount" class="form-control" min="1" required
                               value="{{ old('headcount', 1) }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="reason">缺编原因</label>
                        <select id="reason" name="reason" class="form-select">
                            <option value="">（未填写）</option>
                            @foreach($reasons as $reason)
                                <option value="{{ $reason }}" {{ old('reason') === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="employment_type">用工形式</label>
                        <select id="employment_type" name="employment_type" class="form-select">
                            @foreach($employmentTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('employment_type', 'full_time') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="expect_date">期望到岗日期</label>
                        <input type="date" id="expect_date" name="expect_date" class="form-control" value="{{ old('expect_date') }}">
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label" for="salary_range_min">薪资下限</label>
                        <input type="number" step="0.01" id="salary_range_min" name="salary_range_min" class="form-control"
                               value="{{ old('salary_range_min') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="salary_range_max">薪资上限</label>
                        <input type="number" step="0.01" id="salary_range_max" name="salary_range_max" class="form-control"
                               value="{{ old('salary_range_max') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="status">状态</label>
                        <select id="status" name="status" class="form-select">
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('status', 'open') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="remark">备注</label>
                        <input type="text" id="remark" name="remark" class="form-control" value="{{ old('remark') }}">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                    <a href="{{ route('app.hr.recruitments.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
