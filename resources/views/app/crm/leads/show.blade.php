@extends('layouts.business')
@section('title', '线索详情')
@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6 mb-4">
        <h2 class="text-xl font-bold">{{ $lead->name }}</h2>
        <div class="grid grid-cols-3 gap-4 mt-4 text-sm">
            <div><span class="text-gray-500">联系人：</span>{{ $lead->contact_person ?: '—' }}</div>
            <div><span class="text-gray-500">电话：</span>{{ $lead->phone ?: '—' }}</div>
            <div><span class="text-gray-500">邮箱：</span>{{ $lead->email ?: '—' }}</div>
            <div><span class="text-gray-500">公司：</span>{{ $lead->company ?: '—' }}</div>
            <div><span class="text-gray-500">来源：</span>{{ $lead->source }}</div>
            <div><span class="text-gray-500">状态：</span>{{ $lead->status }}</div>
        </div>
        @if($lead->remark)
        <p class="mt-3 text-sm text-gray-600">{{ $lead->remark }}</p>
        @endif
    </div>

    {{-- 跟进记录 --}}
    <div class="bg-white rounded-lg shadow p-6 mb-4">
        <h3 class="font-bold mb-3">跟进记录</h3>
        @foreach($lead->followUps as $fu)
        <div class="border-l-4 border-blue-400 pl-3 py-2 mb-2 text-sm">
            <span class="text-gray-400 text-xs">{{ $fu->created_at->format('Y-m-d H:i') }} [{{ $fu->type }}]</span>
            <p>{{ $fu->content }}</p>
        </div>
        @endforeach

        <form method="POST" action="{{ route('app.crm.leads.follow-up', $lead) }}" class="mt-4">
            @csrf
            <div class="flex gap-2">
                <select name="type" class="border rounded px-3 py-2 text-sm">
                    <option value="note">备注</option>
                    <option value="call">电话</option>
                    <option value="email">邮件</option>
                    <option value="meeting">拜访</option>
                </select>
                <input type="text" name="content" required placeholder="填写跟进内容..." class="flex-1 border rounded px-3 py-2">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">添加</button>
            </div>
        </form>
    </div>
</div>
@endsection
