@extends('layouts.business')
@section('title', '销售漏斗')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-6">
        <h2 class="text-xl font-bold">销售漏斗</h2>
        <div class="flex gap-2">
            <a href="{{ route('app.crm.leads') }}" class="bg-gray-600 text-white px-4 py-2 rounded">线索</a>
            <a href="{{ route('app.crm.opportunities') }}" class="bg-gray-600 text-white px-4 py-2 rounded">商机</a>
        </div>
    </div>

    <div class="grid grid-cols-6 gap-4">
        @foreach($pipeline as $stage)
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="font-bold text-sm text-gray-700 mb-1">{{ $stage['label'] }}</h3>
            <p class="text-2xl font-bold text-blue-600">{{ $stage['count'] }}</p>
            <p class="text-xs text-gray-500">¥{{ number_format($stage['value'], 0) }}</p>
            <div class="mt-3 space-y-2">
                @foreach($stage['opps'] as $opp)
                <div class="bg-white p-2 rounded shadow-sm text-xs">
                    <p class="font-medium">{{ $opp->name }}</p>
                    <p class="text-gray-500">¥{{ number_format($opp->expected_amount, 0) }} · {{ $opp->probability }}%</p>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
