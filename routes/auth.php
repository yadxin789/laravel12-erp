<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/login', function () {
    $loginField = request()->validate([
        'email' => ['required'],
        'password' => ['required'],
    ]);

    $input = $loginField['email'];
    $password = $loginField['password'];

    // 支持用户名或邮箱登录：同时搜索 name 和 email 字段
    $user = \App\Models\User::where('name', $input)
        ->orWhere('email', $input)
        ->first();

    if ($user && Hash::check($password, $user->password)) {
        Auth::login($user);
        request()->session()->regenerate();

        // 按角色跳转到对应首页
        if ($user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->hasRole('sales_manager')) {
            return redirect()->intended(route('app.sales.orders.index'));
        } elseif ($user->hasRole('purchase_manager')) {
            return redirect()->intended(route('app.purchase.orders.index'));
        } elseif ($user->hasRole('production_manager')) {
            return redirect()->intended(route('app.production.orders.index'));
        } elseif ($user->hasRole('warehouse_manager')) {
            return redirect()->intended(route('app.inventory.index'));
        } elseif ($user->hasRole('quality_manager')) {
            return redirect()->intended(route('app.quality.inspections.index'));
        } else {
            // accountant, viewer 等 → 通用仪表盘
            return redirect()->intended(route('app.dashboard'));
        }
    }

    return back()->withErrors([
        'email' => '用户名或密码错误，请重试。',
    ])->onlyInput('email');
});

Route::post('/register', function () {
    return redirect()->route('login');
});

// 退出登录：同时接受 GET/POST，避免旧页面缓存的 CSRF token 失效后无法退出
Route::match(['get', 'post'], '/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->name('logout');
