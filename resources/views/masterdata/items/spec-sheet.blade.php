@extends('layouts.app')

@section('title', '物料规格书')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-file-pdf"></i>物料规格书</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.items.show', $item) }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>返回物料详情
            </a>
            <a href="{{ route('admin.items.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-list me-1"></i>物料列表
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-5">
            {{-- 物料基本信息 --}}
            <div class="card">
                <div class="card-header"><i class="fa-solid fa-box me-1"></i>物料信息</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="text-muted small">物料编码</div>
                            <div class="fw-semibold font-monospace">{{ $item->code }}</div>
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
                            <div class="text-muted small">单位</div>
                            <div class="fw-semibold">{{ $item->unit?->name ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 规格书状态与上传 --}}
            <div class="card mt-4">
                <div class="card-header"><i class="fa-solid fa-paperclip me-1"></i>规格书文件</div>
                <div class="card-body">
                    @if($item->hasSpecSheet())
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border rounded p-3 mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-3 text-danger"><i class="fa-solid fa-file-pdf"></i></span>
                                <div>
                                    <div class="fw-semibold">规格书-{{ $item->code }}.pdf</div>
                                    <div class="text-muted small">
                                        @if($size)
                                            {{ $size >= 1048576 ? number_format($size / 1048576, 2) . ' MB' : number_format($size / 1024, 1) . ' KB' }}
                                        @endif
                                        @if($uploadedAt)
                                            · 更新于 {{ date('Y-m-d H:i', $uploadedAt) }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.items.spec-sheet.preview', $item) }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="fa-solid fa-eye me-1"></i>预览
                                </a>
                                <a href="{{ route('admin.items.spec-sheet.download', $item) }}" class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-download me-1"></i>下载
                                </a>
                                <form action="{{ route('admin.items.spec-sheet.destroy', $item) }}" method="POST"
                                      onsubmit="return confirm('确定要删除该物料的规格书吗？删除后不可恢复。')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fa-solid fa-trash-can me-1"></i>删除
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted border rounded py-4 mb-3">
                            <div class="fs-2 mb-2"><i class="fa-solid fa-file-pdf"></i></div>
                            <div class="small">暂无规格书</div>
                        </div>
                    @endif

                    <form action="{{ route('admin.items.spec-sheet.upload', $item) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label for="spec_sheet" class="form-label fw-semibold">
                            {{ $item->hasSpecSheet() ? '重新上传规格书' : '上传规格书' }}
                        </label>
                        <input type="file" id="spec_sheet" name="spec_sheet"
                               class="form-control @error('spec_sheet') is-invalid @enderror"
                               accept="application/pdf,.pdf" required>
                        @error('spec_sheet')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">仅支持 PDF 格式文件，大小不超过 20MB。重新上传会替换原有规格书。</div>
                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="fa-solid fa-cloud-arrow-up me-1"></i>上传规格书
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- 在线预览 --}}
        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-eye me-1"></i>在线预览</div>
                <div class="card-body p-0">
                    @if($item->hasSpecSheet() && $size)
                        <iframe src="{{ route('admin.items.spec-sheet.preview', $item) }}"
                                title="物料规格书预览" class="w-100 border-0 rounded-bottom"
                                style="height: 72vh; min-height: 480px;"></iframe>
                    @else
                        <div class="text-center text-muted py-5">
                            <div class="fs-1 mb-3"><i class="fa-solid fa-file-pdf"></i></div>
                            <p class="mb-0">尚未上传规格书，上传后可直接在此预览</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
