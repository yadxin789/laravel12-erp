@extends('layouts.business')
@section('title', '通知中心')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">通知中心</h2>
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="text-blue-500 hover:text-blue-700 text-sm">全部标记为已读</button>
        </form>
    </div>
    @forelse($notifications as $notification)
    <div class="border rounded p-4 mb-3 {{ $notification->is_read ? 'bg-gray-50' : 'bg-blue-50 border-blue-200' }}">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    @if(!$notification->is_read)
                        <span class="w-2 h-2 bg-blue-500 rounded-full inline-block"></span>
                    @endif
                    <span class="text-xs px-2 py-0.5 rounded bg-{{ $notification->type_color }}-100 text-{{ $notification->type_color }}-700">
                        {{ $notification->type }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                </div>
                <h4 class="font-semibold text-gray-800">{{ $notification->title }}</h4>
                <p class="text-gray-600 text-sm mt-1">{{ $notification->message }}</p>
            </div>
            <div class="ml-4">
                <a href="{{ route('notifications.read', $notification) }}" class="text-blue-500 hover:text-blue-700 text-sm whitespace-nowrap">
                    @if($notification->link) 查看详情 @else 标记已读 @endif
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="text-gray-500 text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <p class="mt-2">暂无通知</p>
    </div>
    @endforelse
    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
