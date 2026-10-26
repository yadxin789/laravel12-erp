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

                        <button type="submit" class="btn btn-primary w-100" id="submit-btn">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>开始识别
                        </button>
                    </form>

                    {{-- 异步识别结果区：由 JS 轮询填充，无 JS 时自动回退为同步识别 --}}
                    <div id="ocr-result" class="mt-4 d-none">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="spinner-border spinner-border-sm text-primary" id="ocr-spinner"></span>
                            <span id="ocr-status" class="fw-semibold">已提交，排队中…</span>
                        </div>
                        <div id="ocr-body"></div>
                    </div>

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

// 异步识别：提交到队列后立即返回，轮询结果，避免长时间占用 PHP-FPM
(function () {
    const form = document.querySelector('form[action="{{ route('app.ai.invoice-ocr.recognize') }}"]');
    if (!form) return;

    const btn = document.getElementById('submit-btn');
    const box = document.getElementById('ocr-result');
    const statusText = document.getElementById('ocr-status');
    const spinner = document.getElementById('ocr-spinner');
    const body = document.getElementById('ocr-body');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const data = new FormData(form);
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>提交中…';

        fetch('{{ route('app.ai.ocr.store') }}', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': data.get('_token'), 'Accept': 'application/json'},
            body: data
        })
        .then(function (resp) {
            if (!resp.ok) {
                return resp.json().then(function (json) { throw new Error(json.message || '提交失败'); });
            }
            return resp.json();
        })
        .then(function (json) {
            box.classList.remove('d-none');
            poll(json.status_url);
        })
        .catch(function (err) {
            // 任何异常都回退到同步识别，保证功能可用
            alert(err.message || '异步识别失败，改用同步识别');
            form.submit();
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = original;
        });
    });

    function poll(url) {
        const timer = setInterval(function () {
            fetch(url, {headers: {'Accept': 'application/json'}})
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    statusText.textContent = json.status_label || '处理中…';

                    if (json.status === 'success') {
                        clearInterval(timer);
                        spinner.classList.add('d-none');
                        body.innerHTML = render(json);
                    } else if (json.status === 'failed') {
                        clearInterval(timer);
                        spinner.classList.add('d-none');
                        body.innerHTML = '<div class="alert alert-danger mb-0">识别失败：'
                            + escapeHtml(json.error_message || '未知错误') + '</div>';
                    }
                })
                .catch(function () { clearInterval(timer); });
        }, 2000);
    }

    function render(json) {
        let html = '';
        if (json.image_url) {
            html += '<img src="' + json.image_url + '" class="img-fluid rounded border mb-3" style="max-height:16rem;">';
        }
        html += '<div class="alert alert-success">识别完成，结果需人工核对后再生成订单。</div>';
        html += '<pre class="bg-light border rounded p-3 mb-0" style="max-height:24rem;overflow:auto;">'
            + escapeHtml(JSON.stringify(json.result, null, 2)) + '</pre>';
        return html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text === null || text === undefined ? '' : String(text);
        return div.innerHTML;
    }
})();
</script>
@endpush
