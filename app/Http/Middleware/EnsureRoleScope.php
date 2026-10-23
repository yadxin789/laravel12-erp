<?php

namespace App\Http\Middleware;

use App\Support\RoleScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 页面守卫：角色只能进入自己业务范围内的页面。
 *
 * - 管理员只进后台（admin.*），访问业务页一律回后台首页并提示；
 * - 业务角色访问其它角色的业务模块，GET 请求回到业务仪表盘并提示，
 *   写操作直接 403，避免出现"能点进去但报错"的体验。
 */
class EnsureRoleScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();

        if (! $user || ! $routeName) {
            return $next($request);
        }

        if (RoleScope::allows($routeName, $user)) {
            return $next($request);
        }

        $message = $user->hasRole('admin')
            ? '管理员账号仅用于后台管理与系统维护，不参与业务系统的操作页面。'
            : '当前角色无权访问该业务模块，已返回您的业务首页。';

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'error' => $message], 403);
        }

        if (! $request->isMethod('GET')) {
            abort(403, $message);
        }

        return redirect()->to(RoleScope::home($user))->with('warning', $message);
    }
}
