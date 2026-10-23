<!DOCTYPE html>
<html lang="zh-CN">
<head>
    @include('layouts.partials.head', ['title' => '注册 · ERP企业资源管理系统'])
</head>
<body class="erp-auth-page">
    <div class="erp-auth-card">
        <div class="text-center mb-4">
            <div class="erp-auth-logo"><i class="fa-solid fa-user-plus"></i></div>
            <h1 class="h4 fw-bold mb-1">ERP企业资源管理系统</h1>
            <p class="text-muted small mb-0">创建新账户</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger d-flex align-items-start" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2 mt-1"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="name">用户名</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       class="form-control" placeholder="请输入用户名" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">邮箱</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="form-control" placeholder="请输入邮箱" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">密码</label>
                <input type="password" id="password" name="password"
                       class="form-control" placeholder="请输入密码" required>
            </div>

            <div class="mb-4">
                <label class="form-label" for="password_confirmation">确认密码</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="form-control" placeholder="请确认密码" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="fa-solid fa-user-plus me-1"></i>注册
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="text-decoration-none small">已有账户？立即登录</a>
        </div>
    </div>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
