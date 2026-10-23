@extends('layouts.business')

@section('title', '编辑应聘者')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-users-viewfinder"></i>编辑应聘者</h2>
        <a href="{{ route('app.hr.applications.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.hr.applications.update', $application) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="app_no">应聘编号</label>
                        <input type="text" id="app_no" name="app_no" class="form-control"
                               value="{{ old('app_no', $application->app_no) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="name">姓名 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required
                               value="{{ old('name', $application->name) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="phone">联系电话</label>
                        <input type="text" id="phone" name="phone" class="form-control"
                               value="{{ old('phone', $application->phone) }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="position_id">应聘岗位</label>
                        <select id="position_id" name="position_id" class="form-select">
                            <option value="">（未指定）</option>
                            @foreach($positions as $p)
                                <option value="{{ $p->id }}" {{ old('position_id', $application->position_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="recruitment_id">对应招聘需求</label>
                        <select id="recruitment_id" name="recruitment_id" class="form-select">
                            <option value="">（未关联）</option>
                            @foreach($recruitments as $r)
                                <option value="{{ $r->id }}" {{ old('recruitment_id', $application->recruitment_id) == $r->id ? 'selected' : '' }}>
                                    {{ $r->req_no }} {{ $r->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="source">来源渠道</label>
                        <select id="source" name="source" class="form-select">
                            <option value="">（未填写）</option>
                            @foreach($sources as $source)
                                <option value="{{ $source }}" {{ old('source', $application->source) === $source ? 'selected' : '' }}>{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label" for="education">学历</label>
                        <select id="education" name="education" class="form-select">
                            <option value="">（未填写）</option>
                            @foreach($educations as $edu)
                                <option value="{{ $edu }}" {{ old('education', $application->education) === $edu ? 'selected' : '' }}>{{ $edu }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="experience_years">工作年限</label>
                        <input type="number" id="experience_years" name="experience_years" class="form-control" min="0"
                               value="{{ old('experience_years', $application->experience_years) }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="expect_salary">期望月薪</label>
                        <input type="number" step="0.01" id="expect_salary" name="expect_salary" class="form-control"
                               value="{{ old('expect_salary', $application->expect_salary) }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="status">应聘状态</label>
                        <select id="status" name="status" class="form-select">
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $application->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="email">邮箱</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="{{ old('email', $application->email) }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="interview_at">面试时间</label>
                        <input type="datetime-local" id="interview_at" name="interview_at" class="form-control"
                               value="{{ old('interview_at', $application->interview_at?->format('Y-m-d\TH:i')) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="evaluation">面试评价</label>
                        <textarea id="evaluation" name="evaluation" rows="3" class="form-control">{{ old('evaluation', $application->evaluation) }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>更新</button>
                    <a href="{{ route('app.hr.applications.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
