<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '业务系统')</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100">
    <nav class="bg-indigo-600 text-white">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('app.dashboard') }}" class="text-xl font-bold">电子工厂ERP系统</a>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('app.ai.chat') }}" class="bg-white/20 hover:bg-white/30 px-3 py-1 rounded-lg text-sm">🤖 AI 助手</a>
                        <a href="{{ route('app.ai.invoice-ocr') }}" class="bg-white/20 hover:bg-white/30 px-3 py-1 rounded-lg text-sm">📄 发票识别</a>
                        <span>{{ auth()->user()->name }}</span>
                        <span class="text-gray-300">|</span>
                        <span>{{ auth()->user()->roles->first()?->name ?? '用户' }}</span>
                        <a href="{{ route('notifications.index') }}" class="relative text-gray-300 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @php $unreadCount = app(\App\Services\NotificationService::class)->unreadCount(auth()->id()); @endphp
                            @if($unreadCount > 0)
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>
                            @endif
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-300 hover:text-white">退出</button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        @php
            $roleName = auth()->user()?->roles->first()?->name ?? 'viewer';
            $sidebarMap = [
                'sales_manager' => 'sales',
                'purchase_manager' => 'purchase',
                'production_manager' => 'production',
                'warehouse_manager' => 'warehouse',
                'quality_manager' => 'quality',
            ];
            $sidebarName = $sidebarMap[$roleName] ?? $roleName;
        @endphp
        @include('partials.sidebar_' . $sidebarName)

        <main class="flex-1 p-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    </script>
    @livewireScripts
</body>
</html>