@extends('layouts.business')

@section('title', '登记考勤')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-calendar-plus"></i>登记考勤</h2>
        <a href="{{ route('app.hr.attendances.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.hr.attendances.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="employee_id">员工 <span class="text-danger">*</span></label>
                        <select id="employee_id" name="employee_id" class="form-select" required>
                            <option value="">请选择</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}" {{ old('employee_id') == $e->id ? 'selected' : '' }}>
                                    {{ $e->emp_no }} {{ $e->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="date">日期 <span class="text-danger">*</span></label>
                        <input type="date" id="date" name="date" class="form-control" required
                               value="{{ old('date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="status">状态 <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('status', 'normal') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="check_in">上班时间</label>
                        <input type="time" id="check_in" name="check_in" class="form-control" value="{{ old('check_in') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="check_out">下班时间</label>
                        <input type="time" id="check_out" name="check_out" class="form-control" value="{{ old('check_out') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="remark">备注</label>
                        <input type="text" id="remark" name="remark" class="form-control" value="{{ old('remark') }}">
                        <div class="form-text">同一员工同一天只保留一条记录，重复登记会覆盖</div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                    <a href="{{ route('app.hr.attendances.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
