<!DOCTYPE html>
<html lang="zh-CN">
<head>
    @include('layouts.partials.head', ['title' => '登录 · ERP企业资源管理系统'])
</head>
<body class="erp-auth-page">
    <div class="erp-auth-card">
        <div class="text-center mb-4">
            <div class="erp-auth-logo"><i class="fa-solid fa-cubes"></i></div>
            <h1 class="h4 fw-bold mb-1">ERP企业资源管理系统</h1>
            <p class="text-muted small mb-0">登录您的账户</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger d-flex align-items-start" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2 mt-1"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="email">用户名 / 邮箱</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                    <input type="text" id="email" name="email" value="{{ old('email') }}"
                           class="form-control" placeholder="请输入用户名或邮箱" autofocus required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label" for="password">密码</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" id="password" name="password"
                           class="form-control" placeholder="请输入密码" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="fa-solid fa-right-to-bracket me-1"></i>登录
            </button>
        </form>

        @if (config('app.demo_mode'))
            <div class="mt-3">
                <button class="btn btn-link btn-sm p-0 text-decoration-none" type="button"
                        data-bs-toggle="collapse" data-bs-target="#demoAccounts" aria-expanded="false">
                    <i class="fa-solid fa-users-viewfinder me-1"></i>演示账号（点击自动填充）
                </button>
                <div class="collapse mt-2" id="demoAccounts">
                    <div class="border rounded-2 p-2">
                        @foreach ([
                            ['label' => '销售经理', 'email' => 'sales@demo.com'],
                            ['label' => '采购经理', 'email' => 'purchase@demo.com'],
                            ['label' => '生产经理', 'email' => 'production@demo.com'],
                            ['label' => '仓储主管', 'email' => 'warehouse@demo.com'],
                            ['label' => '质检主管', 'email' => 'quality@demo.com'],
                            ['label' => '财务会计', 'email' => 'accounting@demo.com'],
                            ['label' => '只读访客', 'email' => 'viewer@demo.com'],
                            ['label' => '人事专员', 'email' => 'hr@demo.com'],
                            ['label' => '项目经理', 'email' => 'project@demo.com'],
                            ['label' => '资产管理员', 'email' => 'asset@demo.com'],
                            ['label' => '电商运营', 'email' => 'ec@demo.com'],
                            ['label' => '系统管理员', 'email' => 'admin@admin.com'],
                        ] as $account)
                            <button type="button"
                                    class="btn btn-light btn-sm w-100 text-start mb-1 demo-account"
                                    data-email="{{ $account['email'] }}">
                                <span class="fw-semibold">{{ $account['label'] }}</span>
                                <span class="text-muted ms-1 small">{{ $account['email'] }}</span>
                            </button>
                        @endforeach
                        <div class="text-muted small mt-1">统一密码：<code>123456</code></div>
                    </div>
                </div>
            </div>
        @endif

        <div class="text-center mt-4">
            <a href="{{ route('home') }}" class="text-decoration-none small me-3">
                <i class="fa-solid fa-house me-1"></i>返回首页
            </a>
            <a href="{{ route('register') }}" class="text-decoration-none small">
                还没有账户？注册
            </a>
        </div>
    </div>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    @if (config('app.demo_mode'))
        <script>
            document.querySelectorAll('.demo-account').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('email').value = btn.dataset.email;
                    document.getElementById('password').value = '123456';
                });
            });
        </script>
    @endif
</body>
</html>
