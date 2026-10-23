@php
    $navUser = auth()->user();
    $navRole = $navUser?->roles?->first()?->name ?? '';
    $navUnread = 0;
    if ($navUser) {
        try {
            $navUnread = app(\App\Services\NotificationService::class)->unreadCount($navUser->id);
        } catch (\Throwable $e) {
            $navUnread = 0;
        }
    }
@endphp
<nav class="navbar navbar-expand-lg erp-navbar sticky-top">
    <div class="container-fluid px-lg-4">
        <button class="navbar-toggler border-0 d-lg-none me-2" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#erpSidebar"
                aria-controls="erpSidebar" aria-label="打开菜单">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
            <i class="fa-solid fa-cubes"></i>{{ $brandName ?? 'ERP企业资源管理系统' }}
        </a>

        <div class="ms-auto d-flex align-items-center gap-2">
            @auth
                <a href="{{ route('notifications.index') }}" class="erp-bell" title="消息中心">
                    <i class="fa-solid fa-bell"></i>
                    @if($navUnread > 0)
                        <span class="erp-bell-badge">{{ $navUnread > 99 ? '99+' : $navUnread }}</span>
                    @endif
                </a>

                @unless($navUser->hasRole('admin'))
                    <a href="{{ route('app.chat') }}" class="btn btn-light btn-sm" title="群聊">
                        <i class="fa-regular fa-comments"></i>
                    </a>

                    <a href="{{ route('app.ai.chat') }}" class="btn btn-light btn-sm" title="AI 智能助手">
                        <i class="fa-solid fa-robot"></i>
                    </a>
                @endunless

                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        @if($navUser->avatar_url)
                            <img src="{{ $navUser->avatar_url }}" class="erp-user-avatar-img rounded-circle" alt="{{ $navUser->name }}">
                        @else
                            <span class="erp-user-avatar">{{ mb_substr($navUser->name, 0, 1) }}</span>
                        @endif
                        <span class="text-start d-none d-md-block">
                            <span class="erp-user-name d-block">{{ $navUser->name }}</span>
                            <span class="erp-user-role d-block">{{ $navRole }}</span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="{{ route('home') }}"><i class="fa-solid fa-house me-2"></i>网站首页</a></li>
                        @unless($navUser->hasRole('admin'))
                            <li><a class="dropdown-item" href="{{ route('app.dashboard') }}"><i class="fa-solid fa-gauge-high me-2"></i>业务仪表盘</a></li>
                        @endunless
                        <li><a class="dropdown-item" href="{{ route('app.profile.edit') }}"><i class="fa-solid fa-id-card me-2"></i>个人资料</a></li>
                        <li><a class="dropdown-item" href="{{ route('app.profile.avatar') }}"><i class="fa-solid fa-user-circle me-2"></i>我的头像</a></li>
                        @if($navUser->hasRole('admin'))
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gear me-2"></i>系统管理</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i>退出登录
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-right-to-bracket me-1"></i>登录
                </a>
            @endauth
        </div>
    </div>
</nav>
