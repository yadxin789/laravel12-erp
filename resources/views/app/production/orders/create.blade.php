@extends('layouts.business')

@section('title', '新建生产工单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-industry"></i>新建生产工单</h2>
        <a href="{{ route('app.production.orders.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.production.orders.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="item_id">产品 <span class="text-danger">*</span></label>
                        <select id="item_id" name="item_id" class="form-select" required>
                            <option value="">选择产品</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }} - {{ $item->spec }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="quantity">数量 <span class="text-danger">*</span></label>
                        <input type="number" id="quantity" name="quantity" class="form-control" required min="1" value="{{ old('quantity') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="start_date">计划开始日期 <span class="text-danger">*</span></label>
                        <input type="date" id="start_date" name="start_date" class="form-control" required value="{{ old('start_date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="end_date">计划结束日期 <span class="text-danger">*</span></label>
                        <input type="date" id="end_date" name="end_date" class="form-control" required value="{{ old('end_date') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="bom_id">BOM <span class="text-danger">*</span></label>
                        <select id="bom_id" name="bom_id" class="form-select" required>
                            <option value="">选择BOM</option>
                            @foreach($boms as $bom)
                                <option value="{{ $bom->id }}" {{ old('bom_id') == $bom->id ? 'selected' : '' }}>{{ $bom->item?->name ?? ('BOM #' . $bom->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="routing_id">工艺路线 <span class="text-danger">*</span></label>
                        <select id="routing_id" name="routing_id" class="form-select" required>
                            <option value="">选择工艺路线</option>
                            @foreach($routings as $routing)
                                <option value="{{ $routing->id }}" {{ old('routing_id') == $routing->id ? 'selected' : '' }}>{{ $routing->item?->name ?? ('工艺路线 #' . $routing->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建工单</button>
                    <a href="{{ route('app.production.orders.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
