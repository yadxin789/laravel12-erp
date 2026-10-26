<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'role.scope' => \App\Http\Middleware\EnsureRoleScope::class,
        ]);

        // 页面守卫：角色只能进入自己业务范围内的页面（admin 只进后台）
        $middleware->web(append: [
            \App\Http\Middleware\EnsureRoleScope::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 应用层未捕获异常即时告警（404 / 422 / 表单校验等业务性异常不告警，避免噪音）
        $exceptions->reportable(function (\Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
                || $e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Auth\Access\AuthorizationException
                || $e instanceof \Illuminate\Auth\AuthenticationException
                || $e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                return;
            }

            try {
                app(\App\Services\AlertService::class)->fire(
                    'exception',
                    'app.exception:' . md5(get_class($e) . '|' . mb_substr($e->getMessage(), 0, 120)),
                    'critical',
                    '应用异常：' . class_basename($e),
                    mb_substr($e->getMessage(), 0, 800) ?: '无异常信息',
                    ['file' => $e->getFile(), 'line' => $e->getLine()]
                );
            } catch (\Throwable $ignored) {
                // 告警通道自身不可用时静默降级，不能影响异常处理流程
            }
        });
    })->create();
