<?php

namespace App\Support;

use App\Models\User;

/**
 * 页面守卫：角色业务范围。
 *
 * 规则（与侧边栏可见范围一一对应，避免出现「菜单能点、进去 403」）：
 *   1. 管理员（admin）只进后台 admin.*，不参与业务系统；
 *   2. 业务角色只能进入「公共 + 业务共享 + 本角色专属」模块；
 *   3. 公共模块（通知、附件、个人资料、主数据详情等）所有登录用户可用。
 *
 * 侧边栏通过 Menu::can() 复用同一份判断，请求侧通过
 * App\Http\Middleware\EnsureRoleScope 中间件拦截越界访问。
 */
class RoleScope
{
    /** 所有登录用户（含管理员）都可访问 */
    public const PUBLIC = '公共';

    /**
     * 路由名虽以 admin. 开头、但属于业务侧「主数据只读详情」的路由。
     * （历史遗留命名，视图里仍按 admin.* 生成 URL，这里显式放行）
     */
    private const PUBLIC_ROUTES = [
        'admin.items.show',
        'admin.customers.show',
        'admin.suppliers.show',
        'admin.warehouses.show',
    ];

    /** 业务角色共享模块（管理员除外） */
    public const SHARED = [
        'dashboard', 'ai', 'chat', 'approvals', 'reports', 'masterdata',
    ];

    /** 各角色专属业务模块 */
    public const SCOPES = [
        'admin' => ['admin'],
        'sales_manager' => ['sales', 'crm', 'finance'],
        'purchase_manager' => ['purchase', 'finance'],
        'production_manager' => ['production', 'inventory'],
        'warehouse_manager' => ['inventory', 'purchase', 'production'],
        'quality_manager' => ['quality', 'purchase', 'production'],
        'accountant' => ['finance', 'sales', 'purchase', 'production', 'inventory', 'quality'],
        // 只读访客：全业务模块只读（含 HR/项目/资产/电商）
        'viewer' => ['sales', 'purchase', 'production', 'inventory', 'quality', 'hr', 'projects', 'assets', 'ecommerce'],
        // 扩展模块的专职岗位：一岗一模块，与销售/采购等核心岗位并列
        'hr_specialist' => ['hr'],
        'project_manager' => ['projects'],
        'asset_manager' => ['assets'],
        'ecommerce_operator' => ['ecommerce'],
    ];

    /**
     * 路由名 → 业务模块
     */
    public static function moduleOf(string $routeName): string
    {
        if (in_array($routeName, self::PUBLIC_ROUTES, true)) {
            return self::PUBLIC;
        }

        if (str_starts_with($routeName, 'app.')) {
            $module = explode('.', $routeName)[1] ?? self::PUBLIC;

            // 个人资料属于账号级功能，管理员同样需要维护自己的资料
            return $module === 'profile' ? self::PUBLIC : $module;
        }

        if (str_starts_with($routeName, 'admin.')) {
            return 'admin';
        }

        // notifications.*、attachments.*、home、login… 全部视为公共
        return self::PUBLIC;
    }

    /**
     * 当前用户是否可以进入该路由（多角色取并集）。
     */
    public static function allows(?string $routeName, ?User $user): bool
    {
        if (! $user || ! $routeName) {
            return true; // 未登录 / 无命名路由交给 auth 与其它中间件
        }

        $module = self::moduleOf($routeName);
        if ($module === self::PUBLIC) {
            return true;
        }

        foreach ($user->roles as $role) {
            if (self::roleAllows($role->name, $module)) {
                return true;
            }
        }

        return false;
    }

    public static function roleAllows(string $role, string $module): bool
    {
        if ($role === 'admin') {
            return $module === 'admin';
        }

        return in_array($module, self::SHARED, true)
            || in_array($module, self::SCOPES[$role] ?? [], true);
    }

    /**
     * 越界后的落脚页。
     */
    public static function home(?User $user): string
    {
        if ($user && $user->hasRole('admin')) {
            return route('admin.dashboard');
        }

        return route('app.dashboard');
    }

    /**
     * 当前用户可进入的业务模块（调试/自检用）。
     */
    public static function modulesFor(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $modules = [self::PUBLIC];
        foreach ($user->roles as $role) {
            if ($role->name === 'admin') {
                $modules[] = 'admin';
                continue;
            }
            $modules = array_merge($modules, self::SHARED, self::SCOPES[$role->name] ?? []);
        }

        return array_values(array_unique($modules));
    }
}
