@extends('layouts.business')

@section('title', '新建商机')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-bullseye"></i>新建商机</h2>
        <a href="{{ route('app.crm.opportunities') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('app.crm.opportunities.store') }}">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label" for="name">商机名称 <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" required
                                       placeholder="如：XX公司电阻采购项目" value="{{ old('name') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="lead_id">关联线索</label>
                                <select id="lead_id" name="lead_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($leads as $l)
                                        <option value="{{ $l->id }}" {{ old('lead_id') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="customer_id">关联客户</label>
                                <select id="customer_id" name="customer_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="expected_amount">预计金额 <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" id="expected_amount" name="expected_amount" class="form-control"
                                       value="{{ old('expected_amount') }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="probability">成交概率 (%)</label>
                                <input type="number" id="probability" name="probability" min="0" max="100" class="form-control"
                                       value="{{ old('probability', 50) }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="expected_close_date">预计成交日期</label>
                                <input type="date" id="expected_close_date" name="expected_close_date" class="form-control"
                                       value="{{ old('expected_close_date') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="stage">阶段 <span class="text-danger">*</span></label>
                                <select id="stage" name="stage" class="form-select" required>
                                    @php
                                        $stages = [
                                            'prospecting' => '初步接触',
                                            'qualification' => '需求确认',
                                            'proposal' => '方案报价',
                                            'negotiation' => '商务谈判',
                                            'closed_won' => '已成交',
                                            'closed_lost' => '已丢失',
                                        ];
                                    @endphp
                                    @foreach($stages as $value => $label)
                                        <option value="{{ $value }}" {{ old('stage', 'prospecting') === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                            <a href="{{ route('app.crm.opportunities') }}" class="btn btn-light">取消</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
