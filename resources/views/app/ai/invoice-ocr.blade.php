@extends('layouts.business')

@section('title', '发票智能识别')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-2">📄 发票智能识别（OCR）</h2>
        <p class="text-sm text-gray-500 mb-6">上传采购发票/账单照片，AI 自动提取供应商、日期、金额、明细，快速生成采购订单草稿</p>

        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('app.ai.invoice-ocr') }}" enctype="multipart/form-data">
            @csrf
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition">
                <input type="file" name="invoice_image" id="invoice_image" accept="image/*" required class="hidden">
                <label for="invoice_image" class="cursor-pointer">
                    <div class="text-5xl mb-3">📷</div>
                    <p class="text-gray-700 font-medium">点击选择发票图片</p>
                    <p class="text-sm text-gray-400 mt-1">支持 JPG / PNG / WebP，最大 10MB</p>
                </label>
            </div>

            <div id="preview" class="mt-4 hidden">
                <img id="preview-img" class="max-h-64 mx-auto rounded shadow">
            </div>

            <button type="submit" class="w-full mt-6 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-medium">
                开始识别
            </button>
        </form>

        <div class="mt-6 p-4 bg-yellow-50 rounded text-sm text-gray-600">
            <b>提示：</b>
            <ul class="list-disc ml-5 mt-1 space-y-1">
                <li>图片越清晰，识别准确率越高</li>
                <li>支持增值税专用发票、普通发票、收据、送货单</li>
                <li>识别结果为草稿，需人工核对后再生成订单</li>
                <li>需先在后台 <b>AI 智能助手</b> 中开启视觉/OCR 功能</li>
            </ul>
        </div>
    </div>
</div>

<script>
document.getElementById('invoice_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('preview').classList.remove('hidden');
            document.getElementById('preview-img').src = ev.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endsection
