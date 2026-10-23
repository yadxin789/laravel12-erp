<?php

namespace Database\Seeders;

use App\Models\MasterData\Department;
use App\Models\MasterData\Position;
use Illuminate\Database\Seeder;

/**
 * 组织架构主数据：部门 + 岗位
 *
 * 部门与岗位只描述组织归属与编制，不参与授权（授权由角色承担）。
 */
class DepartmentPositionSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['code' => 'GM',      'name' => '总经办',     'short_name' => '总经办',   'sort_order' => 10, 'desc' => '公司经营决策与综合管理'],
            ['code' => 'SALES',   'name' => '销售部',     'short_name' => '销售',     'sort_order' => 20, 'desc' => '客户开发、报价、销售订单与发货'],
            ['code' => 'PUR',     'name' => '采购部',     'short_name' => '采购',     'sort_order' => 30, 'desc' => '供应商开发与采购订单、收货、退货'],
            ['code' => 'PROD',    'name' => '生产部',     'short_name' => '生产',     'sort_order' => 40, 'desc' => '生产计划、工单执行与报工'],
            ['code' => 'WH',      'name' => '仓储部',     'short_name' => '仓储',     'sort_order' => 50, 'desc' => '出入库、库存调拨与盘点'],
            ['code' => 'QC',      'name' => '质检部',     'short_name' => '质检',     'sort_order' => 60, 'desc' => '来料、过程与成品检验'],
            ['code' => 'FIN',     'name' => '财务部',     'short_name' => '财务',     'sort_order' => 70, 'desc' => '应收应付、收付款与财务报表'],
            ['code' => 'HR',      'name' => '人事行政部', 'short_name' => '人事',     'sort_order' => 80, 'desc' => '员工档案、考勤与行政事务'],
            ['code' => 'PMO',     'name' => '项目部',     'short_name' => '项目',     'sort_order' => 90, 'desc' => '项目立项、任务分解与工时管理'],
            ['code' => 'EC',      'name' => '电商部',     'short_name' => '电商',     'sort_order' => 100, 'desc' => '电商平台订单归集与履约'],
        ];

        $created = [];
        foreach ($departments as $i => $d) {
            $created[$d['code']] = Department::updateOrCreate(
                ['code' => $d['code']],
                [
                    'name' => $d['name'],
                    'short_name' => $d['short_name'],
                    'description' => $d['desc'],
                    'active' => true,
                    'sort_order' => $d['sort_order'],
                ]
            );
        }

        // role 列的取值来自 App\Support\RoleCatalog（岗位 → 角色的默认映射）
        $positions = [
            ['code' => 'GM_MGR',    'name' => '总经理',     'dept' => 'GM',    'level' => '高管', 'headcount' => 1, 'sort' => 10, 'role' => null],
            ['code' => 'SALES_MGR', 'name' => '销售经理',   'dept' => 'SALES', 'level' => '经理', 'headcount' => 1, 'sort' => 10, 'role' => 'sales_manager'],
            ['code' => 'SALES_REP', 'name' => '销售专员',   'dept' => 'SALES', 'level' => '专员', 'headcount' => 4, 'sort' => 20, 'role' => null],
            ['code' => 'PUR_MGR',   'name' => '采购经理',   'dept' => 'PUR',   'level' => '经理', 'headcount' => 1, 'sort' => 10, 'role' => 'purchase_manager'],
            ['code' => 'PUR_REP',   'name' => '采购专员',   'dept' => 'PUR',   'level' => '专员', 'headcount' => 2, 'sort' => 20, 'role' => null],
            ['code' => 'PROD_MGR',  'name' => '生产经理',   'dept' => 'PROD',  'level' => '经理', 'headcount' => 1, 'sort' => 10, 'role' => 'production_manager'],
            ['code' => 'PROD_OP',   'name' => '生产操作工', 'dept' => 'PROD',  'level' => '员工', 'headcount' => 12, 'sort' => 20, 'role' => null],
            ['code' => 'WH_MGR',    'name' => '仓储主管',   'dept' => 'WH',    'level' => '主管', 'headcount' => 1, 'sort' => 10, 'role' => 'warehouse_manager'],
            ['code' => 'WH_KEEP',   'name' => '仓管员',     'dept' => 'WH',    'level' => '员工', 'headcount' => 3, 'sort' => 20, 'role' => null],
            ['code' => 'QC_MGR',    'name' => '质检主管',   'dept' => 'QC',    'level' => '主管', 'headcount' => 1, 'sort' => 10, 'role' => 'quality_manager'],
            ['code' => 'QC_INSP',   'name' => '检验员',     'dept' => 'QC',    'level' => '员工', 'headcount' => 3, 'sort' => 20, 'role' => null],
            ['code' => 'FIN_MGR',   'name' => '财务主管',   'dept' => 'FIN',   'level' => '经理', 'headcount' => 1, 'sort' => 10, 'role' => 'accountant'],
            ['code' => 'FIN_ACC',   'name' => '会计',       'dept' => 'FIN',   'level' => '专员', 'headcount' => 2, 'sort' => 20, 'role' => null],
            ['code' => 'HR_SPEC',   'name' => '人事专员',   'dept' => 'HR',    'level' => '专员', 'headcount' => 2, 'sort' => 10, 'role' => 'hr_specialist'],
            ['code' => 'PMO_MGR',   'name' => '项目经理',   'dept' => 'PMO',   'level' => '经理', 'headcount' => 2, 'sort' => 10, 'role' => 'project_manager'],
            ['code' => 'ASSET_ADM', 'name' => '资产管理员', 'dept' => 'FIN',   'level' => '专员', 'headcount' => 1, 'sort' => 30, 'role' => 'asset_manager'],
            ['code' => 'EC_OP',     'name' => '电商运营',   'dept' => 'EC',    'level' => '专员', 'headcount' => 2, 'sort' => 10, 'role' => 'ecommerce_operator'],
        ];

        foreach ($positions as $p) {
            Position::updateOrCreate(
                ['code' => $p['code']],
                [
                    'name' => $p['name'],
                    'department_id' => $created[$p['dept']]->id ?? null,
                    'level' => $p['level'],
                    'headcount' => $p['headcount'],
                    'default_role' => $p['role'],
                    'sort_order' => $p['sort'],
                    'active' => true,
                ]
            );
        }

        $this->command?->info('组织架构主数据就绪：' . Department::count() . ' 个部门、' . Position::count() . ' 个岗位');
    }
}
