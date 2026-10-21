@extends('layouts.app')

@section('title', '物料详情')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">物料详情</h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.items.barcode-label', $item) }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">打印条码</a>
            <a href="{{ route('admin.items.edit', $item) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">编辑</a>
            <a href="{{ route('admin.items.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">返回</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex gap-6">
            @if($item->image_url)
                <img src="{{ $item->image_url }}" class="w-32 h-32 object-cover rounded">
            @else
                <div class="w-32 h-32 bg-gray-200 rounded flex items-center justify-center text-gray-400">无图片</div>
            @endif
            <div class="flex-1">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-gray-500">物料编码</span>
                        <p class="font-medium font-mono">{{ $item->code }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">条码</span>
                        <p class="font-medium font-mono text-sm">{{ $item->barcode ?? '未生成' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">物料名称</span>
                        <p class="font-medium">{{ $item->name }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">规格型号</span>
                        <p class="font-medium">{{ $item->spec ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">类别</span>
                        <p class="font-medium">{{ $item->category?->name }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">单位</span>
                        <p class="font-medium">{{ $item->unit?->name }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">标准成本</span>
                        <p class="font-medium">{{ number_format($item->standard_cost, 4) }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">安全库存</span>
                        <p class="font-medium">{{ $item->safety_stock }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">当前库存</span>
                        <p class="font-medium">{{ $item->total_stock }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">批次管理</span>
                        <p class="font-medium">{{ $item->batch_management ? '是' : '否' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">序列号管理</span>
                        <p class="font-medium">{{ $item->serial_management ? '是' : '否' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">需要质检</span>
                        <p class="font-medium">{{ $item->quality_inspection_required ? '是' : '否' }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-gray-500">描述</span>
                    <p class="font-medium">{{ $item->description ?: '—' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
