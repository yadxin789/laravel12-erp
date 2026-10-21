@extends('layouts.business')
@section('title', '邮件智能回复')
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-3xl mx-auto">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">📧 邮件智能回复</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="text-sm text-gray-500">← 返回</a>
    </div>
    <form method="POST" class="mb-6">
        @csrf
        <div class="mb-3">
            <label class="block text-sm mb-1">客户</label>
            <select name="customer_id" class="w-full border rounded px-3 py-2">
                <option value="">—</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="block text-sm mb-1">客户询盘内容</label>
            <textarea name="inquiry" required rows="4" class="w-full border rounded px-3 py-2" placeholder="粘贴客户邮件内容..."></textarea>
        </div>
        <button class="bg-blue-600 text-white px-6 py-2 rounded">AI 生成回复草稿</button>
    </form>
    @if($draft)
    <div class="bg-blue-50 border rounded p-4">
        <h3 class="font-bold mb-2">AI 回复草稿</h3>
        <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $draft }}</p>
    </div>
    @endif
</div>
@endsection
