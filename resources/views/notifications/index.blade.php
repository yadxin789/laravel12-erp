@extends('layouts.business')

@section('title', '通知中心')

@section('content')
    @php
        $typeMap = [
            'success' => 'bg-success-subtle text-success-emphasis border-success-subtle',
            'warning' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
            'danger' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
            'error' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
            'info' => 'bg-info-subtle text-info-emphasis border-info-subtle',
            'logistics' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
            'private_message' => 'bg-info-subtle text-info-emphasis border-info-subtle',
        ];
        $typeIcon = [
            'logistics' => 'fa-truck-fast',
            'private_message' => 'fa-comment-dots',
        ];
        $typeLabel = [
            'success' => '成功',
            'warning' => '警告',
            'danger' => '严重',
            'error' => '错误',
            'info' => '信息',
            'logistics' => '物流',
            'private_message' => '私聊',
        ];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-regular fa-bell"></i>通知中心</h2>
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-check-double me-1"></i>全部标记为已读
            </button>
        </form>
    </div>

    <div class="card">
        <div class="card-body">
            @forelse($notifications as $notification)
                <div class="border rounded p-3 mb-3 {{ ! $notification->is_read ? 'bg-warning-subtle border-warning-subtle' : 'bg-body-tertiary' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <span class="badge border {{ $typeMap[$notification->type] ?? 'bg-light text-secondary border' }}">
                                    @if(isset($typeIcon[$notification->type]))
                                        <i class="fa-solid {{ $typeIcon[$notification->type] }} me-1"></i>
                                    @endif
                                    {{ $typeLabel[$notification->type] ?? $notification->type }}
                                </span>
                                @if(! $notification->is_read)
                                    <span class="badge rounded-pill bg-primary">未读</span>
                                @endif
                            </div>
                            <h5 class="fw-bold mb-1">{{ $notification->title }}</h5>
                            <div class="text-muted small">{{ $notification->message }}</div>
                            @if($notification->link)
                                <a href="{{ route('notifications.read', $notification) }}" class="small text-decoration-none d-inline-block mt-2">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>查看详情
                                </a>
                            @endif
                        </div>
                        <div class="text-end ms-3">
                            <small class="text-muted d-block mb-2">{{ $notification->created_at?->diffForHumans() ?? '-' }}</small>
                            <div class="btn-group-vertical">
                                @if(! $notification->is_read)
                                    <form action="{{ route('notifications.mark-read', $notification) }}" method="POST" class="mb-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary">
                                            <i class="fa-solid fa-check me-1"></i>标为已读
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('notifications.destroy', $notification) }}" method="POST"
                                      onsubmit="return confirm('确定要清除这条消息吗？清除后不可恢复。')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fa-solid fa-trash-can me-1"></i>清除
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <div class="fs-1 mb-3"><i class="fa-regular fa-bell-slash"></i></div>
                    <p class="mb-0">暂无通知</p>
                </div>
            @endforelse

            <div class="d-flex justify-content-end">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
@endsection
