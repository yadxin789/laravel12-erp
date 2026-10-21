@extends('layouts.app')

@section('title', '物料管理')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">物料管理</h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.items.barcode-labels') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">打印条码标签</a>
            <a href="{{ route('admin.items.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">创建物料</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" class="flex flex-wrap gap-4 mb-6">
            <input type="text" name="code" placeholder="物料编码" value="{{ request('code') }}" class="border border-gray-300 rounded px-3 py-2">
            <input type="text" name="name" placeholder="物料名称" value="{{ request('name') }}" class="border border-gray-300 rounded px-3 py-2">
            <input type="text" name="barcode" placeholder="条码" value="{{ request('barcode') }}" class="border border-gray-300 rounded px-3 py-2">
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">搜索</button>
        </form>

        <table class="w-full">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left">图片</th>
                    <th class="px-4 py-2 text-left">编码</th>
                    <th class="px-4 py-2 text-left">条码</th>
                    <th class="px-4 py-2 text-left">名称</th>
                    <th class="px-4 py-2 text-left">类别</th>
                    <th class="px-4 py-2 text-left">单位</th>
                    <th class="px-4 py-2 text-left">库存</th>
                    <th class="px-4 py-2 text-center">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr class="border-b">
                        <td class="px-4 py-2">
                            @if($item->image_url)
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-10 h-10 object-cover rounded">
                            @else
                                <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs">无图</div>
                            @endif
                        </td>
                        <td class="px-4 py-2 font-mono text-sm">{{ $item->code }}</td>
                        <td class="px-4 py-2 font-mono text-xs text-gray-600">{{ $item->barcode ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $item->name }}</td>
                        <td class="px-4 py-2">{{ $item->category?->name }}</td>
                        <td class="px-4 py-2">{{ $item->unit?->name }}</td>
                        <td class="px-4 py-2">{{ $item->total_stock }}</td>
                        <td class="px-4 py-2 text-center whitespace-nowrap">
                            <a href="{{ route('admin.items.show', $item) }}" class="text-blue-600 hover:underline">查看</a>
                            <a href="{{ route('admin.items.edit', $item) }}" class="text-green-600 hover:underline ml-2">编辑</a>
                            <a href="{{ route('admin.items.barcode-label', $item) }}" class="text-purple-600 hover:underline ml-2">条码</a>
                            <form action="{{ route('admin.items.destroy', $item) }}" method="POST" class="inline ml-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">删除</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $items->links() }}
    </div>
@endsection
