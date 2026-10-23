@extends('layouts.business')

@section('title', '编辑绩效评估')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-ranking-star"></i>编辑绩效评估</h2>
        <a href="{{ route('app.hr.performances.index', ['period' => $review->period]) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.hr.performances.update', $review) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="employee_id">员工 <span class="text-danger">*</span></label>
                        <select id="employee_id" name="employee_id" class="form-select" required>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}" {{ old('employee_id', $review->employee_id) == $e->id ? 'selected' : '' }}>
                                    {{ $e->emp_no }} {{ $e->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="period">考核周期 <span class="text-danger">*</span></label>
                        <input type="month" id="period" name="period" class="form-control" required
                               value="{{ old('period', $review->period) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="type">考核类型</label>
                        <select id="type" name="type" class="form-select">
                            @foreach($typeLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('type', $review->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="score">考核得分 <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="score" name="score" class="form-control" required
                               min="0" max="100" value="{{ old('score', $review->score) }}">
                        <div class="form-text">当前等级 {{ $review->grade }} {{ $review->grade_label }}，保存后按分数重新评级</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="reviewer">考核人</label>
                        <input type="text" id="reviewer" name="reviewer" class="form-control"
                               value="{{ old('reviewer', $review->reviewer) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="status">状态</label>
                        <select id="status" name="status" class="form-select">
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $review->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="summary">考核评语</label>
                        <textarea id="summary" name="summary" rows="3" class="form-control">{{ old('summary', $review->summary) }}</textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="improvement">改进建议</label>
                        <textarea id="improvement" name="improvement" rows="3" class="form-control">{{ old('improvement', $review->improvement) }}</textarea>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="reviewed_at">考核日期</label>
                        <input type="date" id="reviewed_at" name="reviewed_at" class="form-control"
                               value="{{ old('reviewed_at', $review->reviewed_at?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>更新</button>
                    <a href="{{ route('app.hr.performances.index', ['period' => $review->period]) }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
