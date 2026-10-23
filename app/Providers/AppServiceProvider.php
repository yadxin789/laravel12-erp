<?php

namespace App\Providers;

use App\Listeners\BusinessEventListener;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 前端已切换到 Bootstrap 5，分页组件同步使用 Bootstrap 5 样式
        Paginator::useBootstrapFive();

        // 统一分页视图：Bootstrap 5 样式 + "共 N 条"摘要，且只有一页时也显示页码条
        Paginator::defaultView('pagination.custom');
        Paginator::defaultSimpleView('pagination.custom');

        // Register event subscriber
        Event::subscribe(BusinessEventListener::class);
    }
}
