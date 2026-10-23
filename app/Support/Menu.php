<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * 菜单可见性判断。
 *
 * 侧边栏里出现的每一个链接都先过一遍这里：只要目标路由带
 * role: / permission: / can: 中间件而当前用户不满足，就把菜单项隐藏，
 * 从根上避免"点进去 403"。
 */
class Menu
{
    public static function can(string $routeName): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $route = Route::getRoutes()->getByName($routeName);
        if (! $route) {
            return false;
        }

        // 页面守卫：不在本角色业务范围内的菜单项直接隐藏（与中间件同一份规则）
        if (! RoleScope::allows($routeName, $user)) {
            return false;
        }

        $middlewares = $route->gatherMiddleware();

        foreach ($middlewares as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }

            // role:admin|sales_manager
            if (str_starts_with($middleware, 'role:')) {
                $allowed = array_values(array_filter(explode('|', substr($middleware, 5))));

                foreach ($user->roles as $role) {
                    if (in_array($role->name, $allowed, true)) {
                        return true;
                    }
                }
                return false;
            }

            // permission:view items|edit items
            if (str_starts_with($middleware, 'permission:')) {
                $required = array_filter(explode('|', substr($middleware, 11)));
                foreach ($required as $permission) {
                    if ($user->can($permission)) {
                        return true;
                    }
                }
                return false;
            }

            // can:viewAny,App\Models\...
            if (str_starts_with($middleware, 'can:')) {
                $ability = substr($middleware, 4);
                if (! \Illuminate\Support\Facades\Gate::allows($ability)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 当前用户的角色标识（用于仪表盘/AI 技能分流）。
     */
    public static function role(): string
    {
        return auth()->user()?->roles?->first()?->name ?? 'viewer';
    }
}
