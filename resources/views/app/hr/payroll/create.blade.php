@extends('layouts.business')

@section('title', '新建工资单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-money-check-dollar"></i>新建工资单</h2>
        <a href="{{ route('app.hr.payroll.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.hr.payroll.store') }}">
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
                        <label class="form-label" for="period">薪资月份 <span class="text-danger">*</span></label>
                        <input type="month" id="period" name="period" class="form-control" required
                               value="{{ old('period', date('Y-m')) }}">
                        <div class="form-text">同一员工同一月份只能有一张工资单</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="status">状态</label>
                        <select id="status" name="status" class="form-select">
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('status', 'draft') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label" for="base_salary">基本工资 <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="base_salary" name="base_salary" class="form-control" required
                               value="{{ old('base_salary') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="allowance">津贴</label>
                        <input type="number" step="0.01" id="allowance" name="allowance" class="form-control"
                               value="{{ old('allowance', 0) }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="bonus">绩效奖金</label>
                        <input type="number" step="0.01" id="bonus" name="bonus" class="form-control"
                               value="{{ old('bonus', 0) }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="overtime_pay">加班费</label>
                        <input type="number" step="0.01" id="overtime_pay" name="overtime_pay" class="form-control"
                               value="{{ old('overtime_pay', 0) }}">
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label" for="social_insurance">社保公积金</label>
                        <input type="number" step="0.01" id="social_insurance" name="social_insurance" class="form-control"
                               value="{{ old('social_insurance', 0) }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="tax">个人所得税</label>
                        <input type="number" step="0.01" id="tax" name="tax" class="form-control" value="{{ old('tax', 0) }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="other_deduction">其他扣款</label>
                        <input type="number" step="0.01" id="other_deduction" name="other_deduction" class="form-control"
                               value="{{ old('other_deduction', 0) }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="net_pay">实发工资</label>
                        <input type="number" step="0.01" id="net_pay" name="net_pay" class="form-control"
                               value="{{ old('net_pay') }}" placeholder="留空自动计算">
                        <div class="form-text">= 应发 − 扣款</div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="paid_at">发放日期</label>
                        <input type="date" id="paid_at" name="paid_at" class="form-control" value="{{ old('paid_at') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="remark">备注</label>
                        <input type="text" id="remark" name="remark" class="form-control" value="{{ old('remark') }}">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                    <a href="{{ route('app.hr.payroll.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
