@extends('layouts.business')

@section('title', '生产计划详情')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">生产计划详情 - {{ $plan->plan_no }}</h2>
        <div class="space-x-2">
            @if($plan->canApprove())
            <form action="{{ route('app.production.plans.approve', $plan) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" onclick="return confirm('确定审批此计划吗？')">审批</button>
            </form>
            @endif
            @if($plan->canConvertToOrders())
            <form action="{{ route('app.production.plans.convert', $plan) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" onclick="return confirm('确定将此计划转为生产工单吗？')">生成工单</button>
            </form>
            @endif
            @if($plan->canUpdate())
            <a href="{{ route('app.production.plans.edit', $plan) }}" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">编辑</a>
            @endif
            <a href="{{ route('app.production.plans.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">返回</a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
            <p class="text-gray-600">计划号</p>
            <p class="font-bold text-lg">{{ $plan->plan_no }}</p>
        </div>
        <div>
            <p class="text-gray-600">状态</p>
            <span class="px-2 py-1 rounded text-sm {{ $plan->status === 'draft' ? 'bg-gray-100 text-gray-800' : ($plan->status === 'approved' ? 'bg-green-100 text-green-800' : ($plan->status === 'converted' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100')) }}">
                {{ $plan->status->label() }}
            </span>
        </div>
        <div>
            <p class="text-gray-600">计划日期</p>
            <p>{{ $plan->plan_date }}</p>
        </div>
        <div>
            <p class="text-gray-600">创建人</p>
            <p>{{ $plan->creator?->name }}</p>
        </div>
        <div>
            <p class="text-gray-600">审批人</p>
            <p>{{ $plan->approver?->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-gray-600">审批时间</p>
            <p>{{ $plan->approved_at ?? '-' }}</p>
        </div>
        <div class="col-span-2">
            <p class="text-gray-600">备注</p>
            <p>{{ $plan->remark ?? '-' }}</p>
        </div>
    </div>

    <h3 class="text-lg font-bold text-gray-800 mb-3">计划明细</h3>
    <table class="w-full mb-6">
        <thead>
            <tr class="border-b bg-gray-50">
                <th class="text-left py-2 px-4">行号</th>
                <th class="text-left py-2 px-4">物料</th>
                <th class="text-right py-2 px-4">数量</th>
                <th class="text-left py-2 px-4">计划日期</th>
                <th class="text-left py-2 px-4">工作中心</th>
                <th class="text-left py-2 px-4">对应销售订单</th>
                <th class="text-left py-2 px-4">备注</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plan->lines as $line)
            <tr class="border-b">
                <td class="py-2 px-4">{{ $line->line_number }}</td>
                <td class="py-2 px-4">{{ $line->item?->name }} - {{ $line->item?->spec }}</td>
                <td class="py-2 px-4 text-right">{{ number_format($line->quantity, 4) }}</td>
                <td class="py-2 px-4">{{ $line->plan_date }}</td>
                <td class="py-2 px-4">{{ $line->workCenter?->name ?? '-' }}</td>
                <td class="py-2 px-4">
                    @if($line->salesOrder)
                    <a href="{{ route('app.sales.orders.show', $line->salesOrder) }}" class="text-blue-600 hover:underline">
                        {{ $line->salesOrder->order_no }}
                    </a>
                    @else
                    -
                    @endif
                </td>
                <td class="py-2 px-4">{{ $line->remark ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="py-4 text-center text-gray-400">暂无明细数据</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($plan->lines->count() > 0)
    <div class="border-t pt-4">
        <h3 class="text-lg font-bold text-gray-800 mb-3">汇总</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <p class="text-gray-600">总计明细行数</p>
                <p class="font-bold text-lg">{{ $plan->lines->count() }}</p>
            </div>
            <div>
                <p class="text-gray-600">总计物料种类</p>
                <p class="font-bold text-lg">{{ $plan->lines->pluck('item_id')->unique()->count() }}</p>
            </div>
            <div>
                <p class="text-gray-600">总计数量</p>
                <p class="font-bold text-lg">{{ number_format($plan->lines->sum('quantity'), 4) }}</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
