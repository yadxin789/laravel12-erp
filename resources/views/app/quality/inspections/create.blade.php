@extends('layouts.business')

@section('title', '新建检验单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-check"></i>新建检验单</h2>
        <a href="{{ route('app.quality.inspections.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.quality.inspections.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="inspection_type">检验类型 <span class="text-danger">*</span></label>
                        <select id="inspection_type" name="inspection_type" class="form-select" required>
                            @foreach($types as $key => $value)
                                <option value="{{ $key }}" {{ old('inspection_type') === $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="inspection_date">检验日期 <span class="text-danger">*</span></label>
                        <input type="date" id="inspection_date" name="inspection_date" class="form-control"
                               value="{{ old('inspection_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="item_id">物料 <span class="text-danger">*</span></label>
                        <select id="item_id" name="item_id" class="form-select" required>
                            <option value="">选择物料</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }} - {{ $item->spec }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="batch_number">批次号</label>
                        <input type="text" id="batch_number" name="batch_number" class="form-control"
                               placeholder="批次号" value="{{ old('batch_number') }}">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="inspected_size">检验数量 <span class="text-danger">*</span></label>
                        <input type="number" id="inspected_size" name="inspected_size" class="form-control" required min="1" value="{{ old('inspected_size') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="qualified_size">合格数量 <span class="text-danger">*</span></label>
                        <input type="number" id="qualified_size" name="qualified_size" class="form-control" required min="0" value="{{ old('qualified_size', 0) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="rejected_size">不合格数量 <span class="text-danger">*</span></label>
                        <input type="number" id="rejected_size" name="rejected_size" class="form-control" required min="0" value="{{ old('rejected_size', 0) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="result">检验结果 <span class="text-danger">*</span></label>
                        <select id="result" name="result" class="form-select" required>
                            @foreach(\App\Enums\QualityResult::options() as $value => $label)
                                <option value="{{ $value }}" {{ old('result') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建检验单</button>
                    <a href="{{ route('app.quality.inspections.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
