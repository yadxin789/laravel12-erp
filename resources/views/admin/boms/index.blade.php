@extends('layouts.admin')

@section('title', 'BOM管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">BOM管理</h2>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">编码</th>
                <th class="text-left py-3">物料名称</th>
                <th class="text-left py-3">版本</th>
                <th class="text-left py-3">生效日期</th>
                <th class="text-left py-3">失效日期</th>
                <th class="text-left py-3">数量</th>
                <th class="text-left py-3">状态</th>
            </tr>
        </thead>
        <tbody>
            @foreach($boms as $bom)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $bom->code }}</td>
                <td class="py-3">{{ $bom->item->name ?? '-' }}</td>
                <td class="py-3">{{ $bom->version }}</td>
                <td class="py-3">{{ $bom->effective_date }}</td>
                <td class="py-3">{{ $bom->expiry_date ?? '-' }}</td>
                <td class="py-3">{{ $bom->quantity }}</td>
                <td class="py-3">{{ $bom->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $boms->links() }}
</div>
@endsection