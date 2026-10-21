@extends('layouts.business')

@section('title', '请购单')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">请购单</h2>
        <a href="{{ route('app.purchase.requisitions.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建请购单</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">请购单号</th>
                <th class="text-left py-3">仓库</th>
                <th class="text-left py-3">请购日期</th>
                <th class="text-center py-3">状态</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requisitions as $requisition)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $requisition->requisition_no }}</td>
                <td class="py-3">{{ $requisition->warehouse?->name }}</td>
                <td class="py-3">{{ $requisition->requisition_date }}</td>
                <td class="py-3 text-center">{{ $requisition->status }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('app.purchase.requisitions.show', $requisition) }}" class="text-blue-600 hover:text-blue-800">查看</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $requisitions->links() }}
</div>
@endsection
