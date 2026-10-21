@extends('layouts.business')
@section('title', '经营日报')
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-3xl mx-auto">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">📰 今日经营日报</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="text-sm text-gray-500">← 返回</a>
    </div>
    <div class="bg-blue-50 border border-blue-200 rounded p-4 text-sm leading-relaxed">
        {{ $report }}
    </div>
    <p class="text-xs text-gray-400 mt-4">生成时间：{{ now()->format('Y-m-d H:i') }}</p>
</div>
@endsection
