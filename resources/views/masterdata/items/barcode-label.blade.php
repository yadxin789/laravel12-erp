<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>物料条码标签 - {{ $item->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        body { font-family: 'Microsoft YaHei', sans-serif; padding: 20px; }
        .label {
            width: 300px;
            border: 2px solid #333;
            padding: 15px;
            margin: 10px auto;
            text-align: center;
            page-break-inside: avoid;
        }
        .label .code { font-size: 14px; font-weight: bold; margin-bottom: 5px; }
        .label .name { font-size: 12px; margin-bottom: 8px; }
        .label .barcode { margin: 10px 0; }
        .label .spec { font-size: 11px; color: #666; }
        .print-btn { text-align: center; margin-bottom: 20px; }
        button { padding: 10px 30px; font-size: 16px; cursor: pointer; }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="print-btn">
        <button onclick="window.print()">打印标签</button>
        <button onclick="window.close()" style="margin-left:10px;">关闭</button>
    </div>

    @php
        $barcode = $item->getOrCreateBarcode();
    @endphp

    <div class="label">
        <div class="code">{{ $item->code }}</div>
        <div class="name">{{ $item->name }}</div>
        @if($item->spec)
            <div class="spec">{{ $item->spec }}</div>
        @endif
        <svg class="barcode" id="barcode"></svg>
    </div>

    <script>
        JsBarcode("#barcode", "{{ $barcode }}", {
            format: "CODE128",
            width: 2,
            height: 60,
            displayValue: true,
            fontSize: 14,
            margin: 10
        });
    </script>
</body>
</html>
