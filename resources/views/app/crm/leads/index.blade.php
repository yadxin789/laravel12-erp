@extends('layouts.business')
@section('title', 'CRM 线索')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">销售线索</h2>
        <div class="flex gap-2">
            <a href="{{ route('app.crm.pipeline') }}" class="bg-purple-600 text-white px-4 py-2 rounded">销售漏斗</a>
            <a href="{{ route('app.crm.opportunities') }}" class="bg-blue-600 text-white px-4 py-2 rounded">商机管理</a>
            <a href="{{ route('app.crm.leads.create') }}" class="bg-green-600 text-white px-4 py-2 rounded">新建线索</a>
        </div>
    </div>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2 text-left">名称</th>
            <th class="px-3 py-2 text-left">联系人</th>
            <th class="px-3 py-2 text-left">电话</th>
            <th class="px-3 py-2 text-left">来源</th>
            <th class="px-3 py-2 text-center">状态</th>
            <th class="px-3 py-2 text-left">负责人</th>
        </tr></thead>
        <tbody>
        @foreach($leads as $lead)
            <tr class="border-b">
                <td class="px-3 py-2"><a href="{{ route('app.crm.leads.show', $lead) }}" class="text-blue-600 hover:underline">{{ $lead->name }}</a></td>
                <td class="px-3 py-2">{{ $lead->contact_person ?: '—' }}</td>
                <td class="px-3 py-2">{{ $lead->phone ?: '—' }}</td>
                <td class="px-3 py-2 text-gray-500">{{ $lead->source }}</td>
                <td class="px-3 py-2 text-center">
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">{{ $lead->status }}</span>
                </td>
                <td class="px-3 py-2">{{ $lead->assignedTo?->name ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $leads->links() }}</div>
</div>
@endsection
