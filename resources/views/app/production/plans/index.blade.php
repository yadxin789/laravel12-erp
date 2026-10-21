@extends('layouts.business')

@section('title', '生产计划')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">生产计划</h2>
        <div class="space-x-2">
            <a href="{{ route('app.production.plans.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建计划</a>
            <form action="{{ route('app.production.plans.mrp') }}" method="POST" class="inline">
                @csrf
                <div class="inline-flex items-center">
                    <input type="number" name="horizon_days" value="30" class="w-20 px-2 py-2 border rounded mr-2" placeholder="天数">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">运行MRP</button>
                </div>
            </form>
        </div>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">计划号</th>
                <th class="text-left py-3">计划日期</th>
                <th class="text-center py-3">明细行数</th>
                <th class="text-left py-3">状态</th>
                <th class="text-left py-3">创建人</th>
                <th class="text-left py-3">创建时间</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $plan)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3 text-blue-600">{{ $plan->plan_no }}</td>
                <td class="py-3">{{ $plan->plan_date }}</td>
                <td class="py-3 text-center">{{ $plan->lines->count() }}</td>
                <td class="py-3 text-center">
                    <span class="px-2 py-1 rounded text-sm {{ $plan->status === 'draft' ? 'bg-gray-100 text-gray-800' : ($plan->status === 'approved' ? 'bg-green-100 text-green-800' : ($plan->status === 'converted' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100')) }}">
                        {{ $plan->status->label() }}
                    </span>
                </td>
                <td class="py-3">{{ $plan->creator?->name }}</td>
                <td class="py-3">{{ $plan->created_at }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('app.production.plans.show', $plan) }}" class="text-blue-600 hover:text-blue-800 mr-2">查看</a>
                    @if($plan->canUpdate())
                    <a href="{{ route('app.production.plans.edit', $plan) }}" class="text-green-600 hover:text-green-800 mr-2">编辑</a>
                    @endif
                    @if($plan->canDelete())
                    <form action="{{ route('app.production.plans.destroy', $plan) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('确定删除此计划吗？')">删除</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr class="border-b">
                <td class="py-8 text-center text-gray-400" colspan="7">暂无生产计划数据，点击"新建计划"创建</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {{ $plans->links() }}
</div>
@endsection
