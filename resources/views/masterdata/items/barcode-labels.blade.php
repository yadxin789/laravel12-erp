<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>批量条码标签</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        body { font-family: 'Microsoft YaHei', sans-serif; padding: 20px; }
        .label {
            width: 280px;
            border: 1.5px solid #333;
            padding: 10px;
            margin: 8px;
            text-align: center;
            display: inline-block;
            page-break-inside: avoid;
            vertical-align: top;
        }
        .label .code { font-size: 12px; font-weight: bold; }
        .label .name { font-size: 11px; margin: 3px 0; }
        .label .barcode { margin: 5px 0; }
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
        <button onclick="window.print()">打印全部标签</button>
    </div>

    @foreach($items as $item)
        @php
            $barcode = $item->getOrCreateBarcode();
        @endphp
        <div class="label">
            <div class="code">{{ $item->code }}</div>
            <div class="name">{{ $item->name }}</div>
            <svg class="barcode" id="bc-{{ $item->id }}"></svg>
        </div>
        <script>
            JsBarcode("#bc-{{ $item->id }}", "{{ $barcode }}", {
                format: "CODE128",
                width: 1.5,
                height: 50,
                displayValue: true,
                fontSize: 12,
                margin: 5
            });
        </script>
    @endforeach
</body>
</html>
