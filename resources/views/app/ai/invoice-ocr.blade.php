@extends('layouts.business')

@section('title', '发票智能识别')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-file-invoice"></i>发票智能识别（OCR）</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-8">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">上传采购发票/账单照片，AI 自动提取供应商、日期、金额、明细，快速生成采购订单草稿</p>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('app.ai.invoice-ocr.recognize') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="border border-2 border-dashed rounded text-center p-5 mb-3">
                            <input type="file" name="invoice_image" id="invoice_image" accept="image/*" required class="d-none">
                            <label for="invoice_image" class="d-block" style="cursor: pointer;">
                                <div class="fs-1 text-secondary mb-2"><i class="fa-solid fa-camera"></i></div>
                                <div class="fw-semibold">点击选择发票图片</div>
                                <div class="text-muted small mt-1">支持 JPG / PNG / WebP，最大 10MB</div>
                            </label>
                        </div>

                        <div id="preview" class="mb-3 d-none text-center">
                            <img id="preview-img" class="img-fluid rounded border" style="max-height: 16rem;" alt="预览">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>开始识别
                        </button>
                    </form>

                    <div class="alert alert-warning mt-4 mb-0">
                        <div class="fw-semibold mb-1">提示</div>
                        <ul class="mb-0 ps-4">
                            <li>图片越清晰，识别准确率越高</li>
                            <li>支持增值税专用发票、普通发票、收据、送货单</li>
                            <li>识别结果为草稿，需人工核对后再生成订单</li>
                            <li>需先在后台 <b>AI 智能助手</b> 中开启视觉/OCR 功能</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('invoice_image').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (ev) {
            document.getElementById('preview').classList.remove('d-none');
            document.getElementById('preview-img').src = ev.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
