<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // ============================================================
        // 权限定义 —— 按模块和岗位职责细致划分
        // ============================================================
        $permissions = [

            // ---------- 物料 (items) ----------
            ['name' => 'view items',          'group' => 'masterdata', 'display' => '查看物料'],
            ['name' => 'create items',        'group' => 'masterdata', 'display' => '创建物料'],
            ['name' => 'edit items',          'group' => 'masterdata', 'display' => '编辑物料'],
            ['name' => 'delete items',        'group' => 'masterdata', 'display' => '删除物料'],

            // ---------- 客户 (customers) ----------
            ['name' => 'view customers',      'group' => 'masterdata', 'display' => '查看客户'],
            ['name' => 'create customers',    'group' => 'masterdata', 'display' => '创建客户'],
            ['name' => 'edit customers',      'group' => 'masterdata', 'display' => '编辑客户'],
            ['name' => 'delete customers',    'group' => 'masterdata', 'display' => '删除客户'],

            // ---------- 供应商 (suppliers) ----------
            ['name' => 'view suppliers',      'group' => 'masterdata', 'display' => '查看供应商'],
            ['name' => 'create suppliers',    'group' => 'masterdata', 'display' => '创建供应商'],
            ['name' => 'edit suppliers',      'group' => 'masterdata', 'display' => '编辑供应商'],
            ['name' => 'delete suppliers',    'group' => 'masterdata', 'display' => '删除供应商'],

            // ---------- 仓库 (warehouses) ----------
            ['name' => 'view warehouses',     'group' => 'masterdata', 'display' => '查看仓库'],
            ['name' => 'create warehouses',   'group' => 'masterdata', 'display' => '创建仓库'],
            ['name' => 'edit warehouses',     'group' => 'masterdata', 'display' => '编辑仓库'],
            ['name' => 'delete warehouses',   'group' => 'masterdata', 'display' => '删除仓库'],

            // ---------- BOM ----------
            ['name' => 'view boms',           'group' => 'masterdata', 'display' => '查看BOM'],
            ['name' => 'create boms',         'group' => 'masterdata', 'display' => '创建BOM'],
            ['name' => 'edit boms',           'group' => 'masterdata', 'display' => '编辑BOM'],
            ['name' => 'approve boms',        'group' => 'masterdata', 'display' => '审核BOM'],
            ['name' => 'delete boms',         'group' => 'masterdata', 'display' => '删除BOM'],

            // ---------- 工艺路线 (routings) ----------
            ['name' => 'view routings',       'group' => 'masterdata', 'display' => '查看工艺路线'],

            // ==================== 销售模块 ====================
            ['name' => 'view sales orders',        'group' => 'sales', 'display' => '查看销售订单'],
            ['name' => 'create sales orders',      'group' => 'sales', 'display' => '创建销售订单'],
            ['name' => 'edit sales orders',        'group' => 'sales', 'display' => '编辑销售订单'],
            ['name' => 'submit sales orders',      'group' => 'sales', 'display' => '提交销售订单'],
            ['name' => 'approve sales orders',     'group' => 'sales', 'display' => '审核销售订单'],
            ['name' => 'cancel sales orders',      'group' => 'sales', 'display' => '取消销售订单'],
            ['name' => 'export sales orders',      'group' => 'sales', 'display' => '导出销售订单'],

            ['name' => 'view sales quotations',    'group' => 'sales', 'display' => '查看报价单'],
            ['name' => 'create sales quotations',  'group' => 'sales', 'display' => '创建报价单'],
            ['name' => 'edit sales quotations',    'group' => 'sales', 'display' => '编辑报价单'],
            ['name' => 'delete sales quotations',  'group' => 'sales', 'display' => '删除报价单'],

            ['name' => 'view sales shipments',     'group' => 'sales', 'display' => '查看发货单'],
            ['name' => 'create sales shipments',   'group' => 'sales', 'display' => '创建发货单'],
            ['name' => 'edit sales shipments',     'group' => 'sales', 'display' => '编辑发货单'],
            ['name' => 'delete sales shipments',   'group' => 'sales', 'display' => '删除发货单'],

            ['name' => 'view sales returns',       'group' => 'sales', 'display' => '查看销售退货'],
            ['name' => 'create sales returns',     'group' => 'sales', 'display' => '创建销售退货'],
            ['name' => 'edit sales returns',       'group' => 'sales', 'display' => '编辑销售退货'],
            ['name' => 'delete sales returns',     'group' => 'sales', 'display' => '删除销售退货'],

            // ==================== 采购模块 ====================
            ['name' => 'view purchase orders',        'group' => 'purchasing', 'display' => '查看采购订单'],
            ['name' => 'create purchase orders',      'group' => 'purchasing', 'display' => '创建采购订单'],
            ['name' => 'edit purchase orders',        'group' => 'purchasing', 'display' => '编辑采购订单'],
            ['name' => 'submit purchase orders',      'group' => 'purchasing', 'display' => '提交采购订单'],
            ['name' => 'approve purchase orders',     'group' => 'purchasing', 'display' => '审核采购订单'],
            ['name' => 'cancel purchase orders',      'group' => 'purchasing', 'display' => '取消采购订单'],
            ['name' => 'receive purchase orders',     'group' => 'purchasing', 'display' => '采购收货'],

            ['name' => 'view purchase requisitions',  'group' => 'purchasing', 'display' => '查看请购单'],
            ['name' => 'create purchase requisitions','group' => 'purchasing', 'display' => '创建请购单'],
            ['name' => 'edit purchase requisitions',  'group' => 'purchasing', 'display' => '编辑请购单'],
            ['name' => 'delete purchase requisitions','group' => 'purchasing', 'display' => '删除请购单'],

            ['name' => 'view purchase receipts',      'group' => 'purchasing', 'display' => '查看收货单'],
            ['name' => 'create purchase receipts',    'group' => 'purchasing', 'display' => '创建收货单'],
            ['name' => 'edit purchase receipts',      'group' => 'purchasing', 'display' => '编辑收货单'],
            ['name' => 'delete purchase receipts',    'group' => 'purchasing', 'display' => '删除收货单'],

            ['name' => 'view purchase returns',       'group' => 'purchasing', 'display' => '查看采购退货'],
            ['name' => 'create purchase returns',     'group' => 'purchasing', 'display' => '创建采购退货'],
            ['name' => 'edit purchase returns',       'group' => 'purchasing', 'display' => '编辑采购退货'],
            ['name' => 'delete purchase returns',     'group' => 'purchasing', 'display' => '删除采购退货'],

            // ==================== 库存模块 ====================
            ['name' => 'view inventory',          'group' => 'inventory', 'display' => '查看库存'],
            ['name' => 'transfer inventory',      'group' => 'inventory', 'display' => '库存调拨'],
            ['name' => 'adjust inventory',        'group' => 'inventory', 'display' => '库存调整'],
            ['name' => 'freeze inventory',        'group' => 'inventory', 'display' => '库存冻结/解冻'],
            ['name' => 'stocktake inventory',     'group' => 'inventory', 'display' => '库存盘点'],
            ['name' => 'trace inventory',         'group' => 'inventory', 'display' => '批次追踪'],

            // ==================== 生产模块 ====================
            ['name' => 'view production orders',      'group' => 'production', 'display' => '查看生产工单'],
            ['name' => 'create production orders',    'group' => 'production', 'display' => '创建生产工单'],
            ['name' => 'edit production orders',      'group' => 'production', 'display' => '编辑生产工单'],
            ['name' => 'release production orders',   'group' => 'production', 'display' => '下达生产工单'],
            ['name' => 'start production orders',     'group' => 'production', 'display' => '开工'],
            ['name' => 'report production orders',    'group' => 'production', 'display' => '汇报生产进度'],
            ['name' => 'complete production orders',  'group' => 'production', 'display' => '完工'],
            ['name' => 'close production orders',     'group' => 'production', 'display' => '关闭生产工单'],
            ['name' => 'cancel production orders',    'group' => 'production', 'display' => '取消生产工单'],

            ['name' => 'view material issues',        'group' => 'production', 'display' => '查看领料单'],
            ['name' => 'create material issues',      'group' => 'production', 'display' => '创建领料单'],
            ['name' => 'edit material issues',        'group' => 'production', 'display' => '编辑领料单'],
            ['name' => 'delete material issues',      'group' => 'production', 'display' => '删除领料单'],

            ['name' => 'view production reports',     'group' => 'production', 'display' => '查看生产汇报'],
            ['name' => 'create production reports',   'group' => 'production', 'display' => '创建生产汇报'],
            ['name' => 'edit production reports',     'group' => 'production', 'display' => '编辑生产汇报'],
            ['name' => 'delete production reports',   'group' => 'production', 'display' => '删除生产汇报'],

            ['name' => 'view production receipts',    'group' => 'production', 'display' => '查看成品入库'],
            ['name' => 'create production receipts',  'group' => 'production', 'display' => '创建成品入库'],
            ['name' => 'edit production receipts',    'group' => 'production', 'display' => '编辑成品入库'],
            ['name' => 'delete production receipts',  'group' => 'production', 'display' => '删除成品入库'],

            // ==================== 质检模块 ====================
            ['name' => 'view quality inspections',    'group' => 'quality', 'display' => '查看质检单'],
            ['name' => 'create quality inspections',  'group' => 'quality', 'display' => '创建质检单'],
            ['name' => 'edit quality inspections',    'group' => 'quality', 'display' => '编辑质检单'],
            ['name' => 'complete quality inspections','group' => 'quality', 'display' => '完成质检'],
            ['name' => 'view quality reports',        'group' => 'quality', 'display' => '查看质量报表'],

            // ==================== 报表 ====================
            ['name' => 'view reports',          'group' => 'reports', 'display' => '查看经营报表'],
            ['name' => 'view dashboard',        'group' => 'reports', 'display' => '查看仪表盘'],

            // ==================== 系统管理 ====================
            ['name' => 'manage users',          'group' => 'system', 'display' => '管理用户'],
            ['name' => 'manage roles',          'group' => 'system', 'display' => '管理角色'],
            ['name' => 'manage permissions',    'group' => 'system', 'display' => '管理权限'],
            ['name' => 'manage settings',       'group' => 'system', 'display' => '系统设置'],
            ['name' => 'view audit logs',       'group' => 'system', 'display' => '查看操作日志'],

            // ==================== 人力资源 ====================
            ['name' => 'view employees',        'group' => 'hr', 'display' => '查看员工档案'],
            ['name' => 'create employees',      'group' => 'hr', 'display' => '创建员工档案'],
            ['name' => 'edit employees',        'group' => 'hr', 'display' => '编辑员工档案'],
            ['name' => 'delete employees',      'group' => 'hr', 'display' => '删除员工档案'],
            ['name' => 'view attendance',       'group' => 'hr', 'display' => '查看考勤'],
            ['name' => 'manage attendance',     'group' => 'hr', 'display' => '管理考勤'],
            ['name' => 'view salaries',         'group' => 'hr', 'display' => '查看工资单'],
            ['name' => 'manage salaries',       'group' => 'hr', 'display' => '管理工资单'],
            ['name' => 'view performance reviews',   'group' => 'hr', 'display' => '查看绩效评估'],
            ['name' => 'manage performance reviews', 'group' => 'hr', 'display' => '管理绩效评估'],
            ['name' => 'view recruitments',     'group' => 'hr', 'display' => '查看招聘'],
            ['name' => 'manage recruitments',   'group' => 'hr', 'display' => '管理招聘需求'],
            ['name' => 'view applications',     'group' => 'hr', 'display' => '查看应聘者'],
            ['name' => 'manage applications',   'group' => 'hr', 'display' => '管理应聘者'],

            // ==================== 项目管理 ====================
            ['name' => 'view projects',         'group' => 'projects', 'display' => '查看项目'],
            ['name' => 'create projects',       'group' => 'projects', 'display' => '创建项目'],
            ['name' => 'edit projects',         'group' => 'projects', 'display' => '编辑项目'],
            ['name' => 'delete projects',       'group' => 'projects', 'display' => '删除项目'],
            ['name' => 'manage project tasks',  'group' => 'projects', 'display' => '管理项目任务'],
            ['name' => 'manage time entries',   'group' => 'projects', 'display' => '管理工时'],

            // ==================== 固定资产 ====================
            ['name' => 'view assets',           'group' => 'assets', 'display' => '查看资产'],
            ['name' => 'create assets',         'group' => 'assets', 'display' => '登记资产'],
            ['name' => 'edit assets',           'group' => 'assets', 'display' => '编辑资产'],
            ['name' => 'delete assets',         'group' => 'assets', 'display' => '删除资产'],

            // ==================== 电商订单 ====================
            ['name' => 'view ecommerce orders',   'group' => 'ecommerce', 'display' => '查看电商订单'],
            ['name' => 'create ecommerce orders', 'group' => 'ecommerce', 'display' => '创建电商订单'],
            ['name' => 'edit ecommerce orders',   'group' => 'ecommerce', 'display' => '编辑电商订单'],
            ['name' => 'sync ecommerce orders',   'group' => 'ecommerce', 'display' => '同步电商订单'],
        ];

        // ------ 创建所有权限（同时写入中文名与分组）------
        foreach ($permissions as $permission) {
            $perm = Permission::firstOrCreate([
                'name' => $permission['name'],
                'guard_name' => 'web',
            ], [
                'name' => $permission['name'],
                'guard_name' => 'web',
                'group' => $permission['group'] ?? null,
                'display' => $permission['display'] ?? null,
            ]);

            // 已存在的权限补齐中文名
            $perm->update([
                'group' => $permission['group'] ?? $perm->group,
                'display' => $permission['display'] ?? $perm->display,
            ]);
        }

        // ============================================================
        // 角色定义 —— 按岗位职责精确分配权限
        // ============================================================

        // ---------- admin：拥有全部权限 ----------
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        // ---------- sales_manager：销售全流程 + 需要查看的关联数据 ----------
        $salesRole = Role::firstOrCreate(['name' => 'sales_manager', 'guard_name' => 'web']);
        $salesRole->syncPermissions([
            // 销售模块全部权限
            'view sales orders', 'create sales orders', 'edit sales orders',
            'submit sales orders', 'approve sales orders', 'cancel sales orders', 'export sales orders',
            'view sales quotations', 'create sales quotations', 'edit sales quotations', 'delete sales quotations',
            'view sales shipments', 'create sales shipments', 'edit sales shipments', 'delete sales shipments',
            'view sales returns', 'create sales returns', 'edit sales returns', 'delete sales returns',
            // 需要查看的基础数据
            'view customers', 'view items',
            // 仪表盘
            'view dashboard',
        ]);

        // ---------- purchase_manager：采购全流程 + 需要查看的关联数据 ----------
        $purchaseRole = Role::firstOrCreate(['name' => 'purchase_manager', 'guard_name' => 'web']);
        $purchaseRole->syncPermissions([
            // 采购模块全部权限
            'view purchase orders', 'create purchase orders', 'edit purchase orders',
            'submit purchase orders', 'approve purchase orders', 'cancel purchase orders', 'receive purchase orders',
            'view purchase requisitions', 'create purchase requisitions', 'edit purchase requisitions', 'delete purchase requisitions',
            'view purchase receipts', 'create purchase receipts', 'edit purchase receipts', 'delete purchase receipts',
            'view purchase returns', 'create purchase returns', 'edit purchase returns', 'delete purchase returns',
            // 需要查看的基础数据
            'view suppliers', 'view items',
            // 仪表盘
            'view dashboard',
        ]);

        // ---------- production_manager：生产全流程 + 需要查看的关联数据 ----------
        $productionRole = Role::firstOrCreate(['name' => 'production_manager', 'guard_name' => 'web']);
        $productionRole->syncPermissions([
            // 生产模块全部权限
            'view production orders', 'create production orders', 'edit production orders',
            'release production orders', 'start production orders', 'report production orders',
            'complete production orders', 'close production orders', 'cancel production orders',
            'view material issues', 'create material issues', 'edit material issues', 'delete material issues',
            'view production reports', 'create production reports', 'edit production reports', 'delete production reports',
            'view production receipts', 'create production receipts', 'edit production receipts', 'delete production receipts',
            // 需要查看的基础数据和技术数据
            'view items', 'view boms', 'view routings', 'view warehouses', 'view inventory', 'trace inventory',
            // 仪表盘
            'view dashboard',
        ]);

        // ---------- warehouse_manager：库存管理 + 收货入库 + 发料出库 ----------
        $warehouseRole = Role::firstOrCreate(['name' => 'warehouse_manager', 'guard_name' => 'web']);
        $warehouseRole->syncPermissions([
            // 库存模块全部权限
            'view inventory', 'transfer inventory', 'adjust inventory',
            'freeze inventory', 'stocktake inventory', 'trace inventory',
            // 采购收货（仓库执行）
            'receive purchase orders', 'view purchase orders', 'view purchase receipts',
            'create purchase receipts', 'edit purchase receipts',
            // 生产领料/发料（仓库执行）
            'view production orders', 'view material issues',
            'create material issues', 'edit material issues',
            // 生产入库确认（仓库执行）
            'view production receipts', 'create production receipts', 'edit production receipts',
            // 需要查看的基础数据
            'view items', 'view warehouses', 'view boms',
            // 仪表盘
            'view dashboard',
        ]);

        // ---------- quality_manager：质检全流程 ----------
        $qualityRole = Role::firstOrCreate(['name' => 'quality_manager', 'guard_name' => 'web']);
        $qualityRole->syncPermissions([
            // 质检模块全部权限
            'view quality inspections', 'create quality inspections',
            'edit quality inspections', 'complete quality inspections',
            'view quality reports',
            // 需要查看的关联数据
            'view items', 'view purchase orders', 'view production orders', 'view inventory', 'trace inventory',
            // 仪表盘
            'view dashboard',
        ]);

        // ---------- accountant：财务报表 + 各模块只读 ----------
        $accountantRole = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountantRole->syncPermissions([
            // 报表和仪表盘
            'view reports', 'view dashboard',
            // 财务需查看各业务模块数据（只读）
            'view sales orders', 'view sales quotations', 'view sales shipments', 'view sales returns',
            'view purchase orders', 'view purchase requisitions', 'view purchase receipts', 'view purchase returns',
            'view inventory',
            'view production orders', 'view material issues', 'view production reports', 'view production receipts',
            'view quality inspections', 'view quality reports',
            'view items', 'view customers', 'view suppliers', 'view warehouses',
            'view boms', 'view routings',
        ]);

        // ---------- viewer：全模块只读 ----------
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewerRole->syncPermissions(Permission::where('name', 'like', 'view %')->get());

        // ---------- hr_specialist：人事专员（员工档案 + 考勤）----------
        $hrRole = Role::firstOrCreate(['name' => 'hr_specialist', 'guard_name' => 'web']);
        $hrRole->syncPermissions([
            'view employees', 'create employees', 'edit employees', 'delete employees',
            'view attendance', 'manage attendance',
            'view salaries', 'manage salaries',
            'view performance reviews', 'manage performance reviews',
            'view recruitments', 'manage recruitments',
            'view applications', 'manage applications',
            // 需要查看的基础数据与仪表盘
            'view items', 'view dashboard',
        ]);

        // ---------- project_manager：项目经理（项目 + 任务 + 工时）----------
        $pmRole = Role::firstOrCreate(['name' => 'project_manager', 'guard_name' => 'web']);
        $pmRole->syncPermissions([
            'view projects', 'create projects', 'edit projects', 'delete projects',
            'manage project tasks', 'manage time entries',
            // 需要查看的关联数据
            'view customers', 'view items', 'view dashboard',
        ]);

        // ---------- asset_manager：资产管理员（资产台账 + 折旧）----------
        $amRole = Role::firstOrCreate(['name' => 'asset_manager', 'guard_name' => 'web']);
        $amRole->syncPermissions([
            'view assets', 'create assets', 'edit assets', 'delete assets',
            // 需要查看的关联数据
            'view items', 'view warehouses', 'view dashboard',
        ]);

        // ---------- ecommerce_operator：电商运营（订单归集 + 同步）----------
        $ecRole = Role::firstOrCreate(['name' => 'ecommerce_operator', 'guard_name' => 'web']);
        $ecRole->syncPermissions([
            'view ecommerce orders', 'create ecommerce orders', 'edit ecommerce orders', 'sync ecommerce orders',
            // 需要查看的关联数据
            'view items', 'view customers', 'view dashboard',
        ]);

        // ============================================================
        // 角色 ↔ 岗位中文名（界面展示用，可在后台「角色管理」中修改）
        // ============================================================
        foreach (Role::whereNull('display_name')->orWhere('display_name', '')->get() as $role) {
            $meta = \App\Support\RoleCatalog::CATALOG[$role->name] ?? null;
            if ($meta) {
                $role->update(['display_name' => $meta['label']]);
            }
        }
    }
}
