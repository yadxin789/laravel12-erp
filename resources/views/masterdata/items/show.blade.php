@extends('layouts.app')

@section('title', '物料详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-box"></i>物料详情</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.items.spec-sheet', $item) }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-file-pdf me-1"></i>上传规格书
            </a>
            <a href="{{ route('admin.items.barcode-label', $item) }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-barcode me-1"></i>打印条码
            </a>
            <a href="{{ route('admin.items.edit', $item) }}" class="btn btn-primary">
                <i class="fa-regular fa-pen-to-square me-1"></i>编辑
            </a>
            <a href="{{ route('admin.items.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-12 col-lg-3 text-center">
                    @if($item->image_url)
                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="rounded border" width="128" height="128" style="object-fit: cover;">
                    @else
                        <div class="text-muted small border rounded d-flex align-items-center justify-content-center mx-auto"
                             style="width: 128px; height: 128px;">无图片</div>
                    @endif
                </div>

                <div class="col-12 col-lg-9">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">物料编码</div>
                            <div class="fw-semibold font-monospace">{{ $item->code }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">条码</div>
                            <div class="fw-semibold font-monospace small">{{ $item->barcode ?? '未生成' }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">物料名称</div>
                            <div class="fw-semibold">{{ $item->name }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">规格型号</div>
                            <div class="fw-semibold">{{ $item->spec ?? '—' }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">类别</div>
                            <div class="fw-semibold">{{ $item->category?->name }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">单位</div>
                            <div class="fw-semibold">{{ $item->unit?->name }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">标准成本</div>
                            <div class="fw-semibold">{{ number_format($item->standard_cost, 4) }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">安全库存</div>
                            <div class="fw-semibold">{{ $item->safety_stock }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">当前库存</div>
                            <div class="fw-semibold">{{ $item->total_stock }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">管理属性</div>
                            <div>
                                <span class="badge {{ $item->batch_management ? 'bg-primary-subtle text-primary-emphasis' : 'bg-light text-secondary' }}">批次管理</span>
                                <span class="badge {{ $item->serial_management ? 'bg-primary-subtle text-primary-emphasis' : 'bg-light text-secondary' }}">序列号管理</span>
                                <span class="badge {{ $item->quality_inspection_required ? 'bg-warning-subtle text-warning-emphasis' : 'bg-light text-secondary' }}">需要质检</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">规格书</div>
                            <div class="fw-semibold">
                                @if($item->hasSpecSheet())
                                    <a href="{{ route('admin.items.spec-sheet', $item) }}" class="text-decoration-none">
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                            <i class="fa-solid fa-file-pdf me-1"></i>已上传 PDF
                                        </span>
                                    </a>
                                    <a href="{{ route('admin.items.spec-sheet.download', $item) }}" class="small ms-2">
                                        <i class="fa-solid fa-download me-1"></i>下载
                                    </a>
                                @else
                                    <span class="badge bg-light text-secondary border">未上传</span>
                                    <a href="{{ route('admin.items.spec-sheet', $item) }}" class="small ms-2">
                                        <i class="fa-solid fa-cloud-arrow-up me-1"></i>去上传
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">描述</div>
                            <div class="fw-semibold">{{ $item->description ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
