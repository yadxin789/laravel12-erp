<?php

namespace App\Support;

use Spatie\Permission\Models\Role;

/**
 * 岗位字典：角色 key ↔ 中文岗位名的唯一映射源。
 *
 * 角色（role）是权限载体，岗位（position）是组织编制，二者分离；
 * 但每个角色在系统里都对应一个明确的岗位名称，用于界面展示：
 * 角色管理、用户管理、审批流、AI 技能分流、群聊成员头衔都从这里取中文名。
 *
 * 若 roles.display_name 有值（管理员可在后台改），优先用它，否则用字典默认值。
 */
class RoleCatalog
{
    /** 角色 key => [岗位中文名, 所属部门, 职责说明] */
    public const CATALOG = [
        'admin' => [
            'label' => '系统管理员',
            'department' => '总经办',
            'desc' => '用户/角色/权限、主数据与系统配置；不参与业务单据',
        ],
        'sales_manager' => [
            'label' => '销售经理',
            'department' => '销售部',
            'desc' => '销售订单、报价、发货、退货 + 客户与发票',
        ],
        'purchase_manager' => [
            'label' => '采购经理',
            'department' => '采购部',
            'desc' => '采购订单、请购、收货、退货 + 供应商账单',
        ],
        'production_manager' => [
            'label' => '生产经理',
            'department' => '生产部',
            'desc' => '生产计划、工单、领料、报工、入库',
        ],
        'warehouse_manager' => [
            'label' => '仓储主管',
            'department' => '仓储部',
            'desc' => '出入库、调拨、盘点、呆滞与缺料监控',
        ],
        'quality_manager' => [
            'label' => '质检主管',
            'department' => '质检部',
            'desc' => '来料/过程/成品检验与不合格品处理',
        ],
        'accountant' => [
            'label' => '财务主管',
            'department' => '财务部',
            'desc' => '应收应付、收付款与经营报表',
        ],
        'hr_specialist' => [
            'label' => '人事专员',
            'department' => '人事行政部',
            'desc' => '员工档案、考勤、薪资、绩效、招聘',
        ],
        'project_manager' => [
            'label' => '项目经理',
            'department' => '项目部',
            'desc' => '项目立项、任务分解与工时归集',
        ],
        'asset_manager' => [
            'label' => '资产管理员',
            'department' => '财务部',
            'desc' => '资产台账、折旧计算与状态流转',
        ],
        'ecommerce_operator' => [
            'label' => '电商运营',
            'department' => '电商部',
            'desc' => '多平台订单归集、同步与履约',
        ],
        'viewer' => [
            'label' => '只读访客',
            'department' => '—',
            'desc' => '全模块只读，用于参观演示与审计查阅',
        ],
    ];

    /**
     * 岗位中文名（优先用后台自定义的 display_name）
     */
    public static function label($role): string
    {
        $name = $role instanceof Role ? $role->name : (string) $role;

        if ($role instanceof Role) {
            $custom = $role->getAttribute('display_name');
            if (! empty($custom)) {
                return $custom;
            }
        }

        return self::CATALOG[$name]['label'] ?? $name;
    }

    /**
     * 角色 key（兼容传入 Role 对象 / 字符串）
     */
    public static function key($role): string
    {
        return $role instanceof Role ? $role->name : (string) $role;
    }

    /**
     * 所属部门中文名
     */
    public static function department($role): string
    {
        return self::CATALOG[self::key($role)]['department'] ?? '—';
    }

    /**
     * 岗位职责说明
     */
    public static function desc($role): string
    {
        return self::CATALOG[self::key($role)]['desc'] ?? '';
    }

    /**
     * 下拉选项：key => "中文岗位名（key）"
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::CATALOG as $key => $meta) {
            $options[$key] = $meta['label'] . '（' . $key . '）';
        }

        return $options;
    }

    /**
     * 是否为已知岗位角色
     */
    public static function has($role): bool
    {
        return isset(self::CATALOG[self::key($role)]);
    }
}
