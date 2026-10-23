@extends('layouts.business')

@section('title', '生产计划')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-calendar-days"></i>生产计划</h2>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('app.production.plans.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i>新建计划
            </a>
            <form action="{{ route('app.production.plans.mrp') }}" method="POST" class="d-flex gap-2">
                @csrf
                <input type="number" name="horizon_days" value="30" min="1" class="form-control" style="width: 90px;" placeholder="天数">
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-gears me-1"></i>运行MRP</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>计划号</th>
                            <th>计划日期</th>
                            <th class="text-center">明细行数</th>
                            <th class="text-center">状态</th>
                            <th>创建人</th>
                            <th>创建时间</th>
                            <th class="text-end" style="width: 180px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $plan)
                            @php
                                $badge = match (true) {
                                    $plan->status === \App\Enums\ProductionPlanStatus::Approved => 'bg-success-subtle text-success-emphasis border border-success-subtle',
                                    $plan->status === \App\Enums\ProductionPlanStatus::Converted => 'bg-primary-subtle text-primary-emphasis border border-primary-subtle',
                                    $plan->status === \App\Enums\ProductionPlanStatus::Closed => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
                                    default => 'bg-light text-secondary border',
                                };
                            @endphp
                            <tr>
                                <td class="font-monospace small">{{ $plan->plan_no }}</td>
                                <td>{{ $plan->plan_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="text-center">{{ $plan->lines->count() }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $badge }}">{{ $plan->status?->label() ?? '-' }}</span>
                                </td>
                                <td>{{ $plan->creator?->name ?? '-' }}</td>
                                <td>{{ $plan->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('app.production.plans.show', $plan) }}" class="btn btn-outline-primary">
                                            <i class="fa-regular fa-eye"></i>查看
                                        </a>
                                        @if($plan->canUpdate())
                                            <a href="{{ route('app.production.plans.edit', $plan) }}" class="btn btn-outline-secondary">
                                                <i class="fa-regular fa-pen-to-square"></i>编辑
                                            </a>
                                        @endif
                                        @if($plan->canDelete())
                                            <form action="{{ route('app.production.plans.destroy', $plan) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('确定删除此计划吗？')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="fa-regular fa-trash-can"></i>删除
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">暂无生产计划数据，点击"新建计划"创建</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $plans->links() }}
            </div>
        </div>
    </div>
@endsection
