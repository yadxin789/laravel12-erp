@extends('layouts.admin')

@section('title', '物料管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">物料管理</h2>
        <a href="{{ route('admin.items.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建物料</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">编码</th>
                <th class="text-left py-3">名称</th>
                <th class="text-left py-3">规格</th>
                <th class="text-left py-3">单位</th>
                <th class="text-left py-3">类型</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $item->code }}</td>
                <td class="py-3">{{ $item->name }}</td>
                <td class="py-3">{{ $item->spec }}</td>
                <td class="py-3">{{ $item->unit->name ?? '-' }}</td>
                <td class="py-3">{{ $item->item_type }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('admin.items.show', $item) }}" class="text-blue-600 hover:text-blue-800 mr-2">查看</a>
                    <a href="{{ route('admin.items.edit', $item) }}" class="text-blue-600 hover:text-blue-800 mr-2">编辑</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $items->links() }}
</div>
@endsection
