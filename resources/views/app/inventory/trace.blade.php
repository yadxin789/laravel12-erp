@extends('layouts.business')

@section('title', '批次追踪')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">批次追踪</h2>
    <form method="POST" action="{{ route('app.inventory.trace.search') }}" class="mb-4">
        @csrf
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-700 mb-2">批次号</label>
                <input type="text" name="batch_number" class="w-full px-4 py-2 border rounded" required placeholder="输入批次号">
            </div>
            <div>
                <label class="block text-gray-700 mb-2">&nbsp;</label>
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">查询</button>
            </div>
        </div>
    </form>
    
    @if(isset($transactions) && $transactions->count() > 0)
    <h3 class="text-lg font-semibold text-gray-800 mb-4">批次 {{ $batch_number }} 的流转记录</h3>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">交易单号</th>
                <th class="text-left py-3">交易类型</th>
                <th class="text-left py-3">仓库</th>
                <th class="text-right py-3">数量</th>
                <th class="text-left py-3">时间</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr class="border-b">
                <td class="py-3">{{ $transaction->transaction_no }}</td>
                <td class="py-3">{{ $transaction->transaction_type }}</td>
                <td class="py-3">{{ $transaction->warehouse?->name }}</td>
                <td class="py-3 text-right">{{ $transaction->quantity }}</td>
                <td class="py-3">{{ $transaction->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="text-gray-500">请输入批次号进行查询</p>
    @endif
</div>
@endsection
