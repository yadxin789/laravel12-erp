<?php

namespace App\Support;

use App\Models\User;

/**
 * 角色化 AI 技能包。
 *
 * 不同角色看到的 AI 聊天页，其"系统提示词 / 可用工具 / 快捷问题"都不同：
 * 销售问订单与欠款，仓库问库存与呆滞料，财务问应收应付……
 * 让 AI 一开始就站在该角色的业务视角上回答问题。
 */
class AiSkills
{
    public static function for(?User $user): array
    {
        $role = $user?->roles?->first()?->name ?? 'viewer';
        $catalog = self::catalog();
        $skill = $catalog[$role] ?? $catalog['viewer'];

        // 岗位中文名统一取岗位字典（后台改过 display_name 时以其为准）
        $skill['label'] = RoleCatalog::CATALOG[$role]['label'] ?? $skill['label'];

        $skill['role'] = $role;
        $skill['user_name'] = $user?->name ?? '同事';
        $skill['system_prompt'] = self::buildPrompt($skill, $skill['user_name']);

        return $skill;
    }

    public static function catalog(): array
    {
        return [
            'admin' => [
                'label' => '系统管理员',
                'icon' => 'fa-user-shield',
                'focus' => '全公司经营状况总览、用户与权限、系统配置与数据健康度',
                'data' => '销售订单、采购订单、生产工单、库存余额、质量检验、应收应付、用户与角色',
                'tools' => [
                    ['id' => 'inventory', 'name' => '库存查询', 'icon' => 'fa-boxes-stacked'],
                    ['id' => 'sales', 'name' => '销售分析', 'icon' => 'fa-chart-line'],
                    ['id' => 'purchase', 'name' => '采购建议', 'icon' => 'fa-truck-fast'],
                    ['id' => 'production', 'name' => '生产排程', 'icon' => 'fa-industry'],
                ],
                'prompts' => [
                    '生成本月经营日报摘要',
                    '哪些用户权限配置需要关注？',
                    '当前各模块数据完整度如何？',
                    '本月销售、采购、生产的核心指标是多少？',
                ],
            ],
            'sales_manager' => [
                'label' => '销售经理',
                'icon' => 'fa-handshake',
                'focus' => '销售订单履约、客户与欠款、报价与毛利、销售漏斗',
                'data' => '销售订单、报价单、发货单、退货单、客户档案、客户发票与回款',
                'tools' => [
                    ['id' => 'sales', 'name' => '销售分析', 'icon' => 'fa-chart-line'],
                    ['id' => 'inventory', 'name' => '库存查询', 'icon' => 'fa-boxes-stacked'],
                    ['id' => 'customer', 'name' => '客户信用', 'icon' => 'fa-user-tie'],
                ],
                'prompts' => [
                    '本月销售额与订单量是多少？',
                    '哪些订单交付即将逾期？',
                    '客户欠款 TOP5 是哪些，账龄多久？',
                    '哪些客户本月没有下单？',
                    '给我一份本周销售跟进清单',
                ],
            ],
            'purchase_manager' => [
                'label' => '采购经理',
                'icon' => 'fa-cart-flatbed',
                'focus' => '采购到货、供应商交期与质量、请购与价格趋势、缺料风险',
                'data' => '采购订单、请购单、收货单、采购退货、供应商档案、供应商账单',
                'tools' => [
                    ['id' => 'purchase', 'name' => '采购建议', 'icon' => 'fa-truck-fast'],
                    ['id' => 'inventory', 'name' => '库存查询', 'icon' => 'fa-boxes-stacked'],
                    ['id' => 'supplier', 'name' => '供应商评估', 'icon' => 'fa-truck-field'],
                ],
                'prompts' => [
                    '哪些采购订单已经逾期未到货？',
                    '哪些物料需要立即补货，建议采购量是多少？',
                    '供应商交期与质量表现排名如何？',
                    '本月采购金额与到货及时率是多少？',
                ],
            ],
            'production_manager' => [
                'label' => '生产经理',
                'icon' => 'fa-industry',
                'focus' => '工单进度与产能、物料齐套、BOM 与工艺、生产成本',
                'data' => '生产工单、生产计划、领料单、生产汇报、成品入库、BOM、工艺路线',
                'tools' => [
                    ['id' => 'production', 'name' => '生产排程', 'icon' => 'fa-industry'],
                    ['id' => 'inventory', 'name' => '齐套检查', 'icon' => 'fa-boxes-stacked'],
                    ['id' => 'bom', 'name' => 'BOM 查询', 'icon' => 'fa-diagram-project'],
                ],
                'prompts' => [
                    '本周生产工单完成情况如何？',
                    '哪些工单缺料，缺口多少？',
                    '给我一个合理的排产顺序建议',
                    '本月生产成本与计划差异在哪里？',
                ],
            ],
            'warehouse_manager' => [
                'label' => '仓储经理',
                'icon' => 'fa-warehouse',
                'focus' => '库存数量与库龄、安全库存与呆滞料、出入库流水、物流在途',
                'data' => '库存余额、出入库流水、批次追溯、盘点记录、物流运单与轨迹',
                'tools' => [
                    ['id' => 'inventory', 'name' => '库存查询', 'icon' => 'fa-boxes-stacked'],
                    ['id' => 'logistics', 'name' => '物流查询', 'icon' => 'fa-truck-fast'],
                    ['id' => 'purchase', 'name' => '补货建议', 'icon' => 'fa-cart-plus'],
                ],
                'prompts' => [
                    '哪些物料库存低于安全库存？',
                    '有没有呆滞料需要处理？',
                    '各仓库当前库存分布如何？',
                    '哪些在途运单即将到货？',
                ],
            ],
            'quality_manager' => [
                'label' => '质量经理',
                'icon' => 'fa-clipboard-check',
                'focus' => '来料与过程检验、不良率、缺陷分布、供应商质量',
                'data' => '检验单、缺陷记录、质量报表、采购订单、生产工单',
                'tools' => [
                    ['id' => 'quality', 'name' => '质量分析', 'icon' => 'fa-clipboard-check'],
                    ['id' => 'supplier', 'name' => '供应商质量', 'icon' => 'fa-truck-field'],
                    ['id' => 'inventory', 'name' => '库存查询', 'icon' => 'fa-boxes-stacked'],
                ],
                'prompts' => [
                    '本月检验合格率是多少？',
                    '缺陷类型 TOP5 是什么，集中在哪些物料？',
                    '哪些供应商来料质量最差？',
                    '给我一份质量改进建议',
                ],
            ],
            'accountant' => [
                'label' => '财务主管',
                'icon' => 'fa-calculator',
                'focus' => '应收应付、账龄与坏账风险、毛利与现金流、对账',
                'data' => '客户发票、供应商账单、收付款记录、往来对账单、销售与采购订单金额',
                'tools' => [
                    ['id' => 'finance', 'name' => '应收应付', 'icon' => 'fa-money-bill-wave'],
                    ['id' => 'sales', 'name' => '销售分析', 'icon' => 'fa-chart-line'],
                    ['id' => 'purchase', 'name' => '采购支出', 'icon' => 'fa-truck-fast'],
                ],
                'prompts' => [
                    '当前应收与应付余额是多少？',
                    '账龄超过 90 天的客户有哪些？',
                    '本月毛利与现金流入情况如何？',
                    '哪些账单需要优先催收？',
                ],
            ],
            'hr_specialist' => [
                'label' => '人事专员',
                'icon' => 'fa-users',
                'focus' => '员工档案与在职状态、考勤异常、薪资与绩效、招聘进度',
                'data' => '员工档案、考勤记录、薪资记录、绩效评估、招聘应聘记录',
                'tools' => [
                    ['id' => 'hr', 'name' => '人力分析', 'icon' => 'fa-users'],
                    ['id' => 'attendance', 'name' => '考勤分析', 'icon' => 'fa-calendar-check'],
                ],
                'prompts' => [
                    '本月考勤异常人次有多少，集中在哪些部门？',
                    '当前在职人数与各部门分布如何？',
                    '哪些员工的绩效评分偏低需要关注？',
                    '本季度招聘录用率是多少？',
                ],
            ],
            'project_manager' => [
                'label' => '项目经理',
                'icon' => 'fa-diagram-project',
                'focus' => '项目进度、任务完成情况、工时投入与项目预算',
                'data' => '项目档案、任务清单、工时记录、项目预算与客户信息',
                'tools' => [
                    ['id' => 'project', 'name' => '项目进度', 'icon' => 'fa-diagram-project'],
                    ['id' => 'timesheet', 'name' => '工时统计', 'icon' => 'fa-clock'],
                ],
                'prompts' => [
                    '哪些项目处于进行中且任务完成率偏低？',
                    '各项目的工时投入与预算对比如何？',
                    '本周有哪些任务即将到期？',
                    '给我一份项目健康度评估',
                ],
            ],
            'asset_manager' => [
                'label' => '资产管理员',
                'icon' => 'fa-boxes-stacked',
                'focus' => '资产台账、折旧计提、闲置与维修资产、资产净值',
                'data' => '资产档案、原值与折旧参数、使用人与存放位置、资产状态',
                'tools' => [
                    ['id' => 'asset', 'name' => '资产台账', 'icon' => 'fa-boxes-stacked'],
                    ['id' => 'depreciation', 'name' => '折旧计算', 'icon' => 'fa-calculator'],
                ],
                'prompts' => [
                    '本月应计提折旧多少？',
                    '哪些资产处于闲置或维修中？',
                    '各分类资产的账面净值是多少？',
                    '哪些资产即将折旧完毕？',
                ],
            ],
            'ecommerce_operator' => [
                'label' => '电商运营',
                'icon' => 'fa-cart-shopping',
                'focus' => '各平台订单量、待同步与待发货、订单金额与履约时效',
                'data' => '电商订单、平台来源、订单明细与金额、同步与发货状态',
                'tools' => [
                    ['id' => 'ecommerce', 'name' => '订单分析', 'icon' => 'fa-cart-shopping'],
                    ['id' => 'inventory', 'name' => '库存查询', 'icon' => 'fa-boxes-stacked'],
                ],
                'prompts' => [
                    '各平台本月订单量与金额对比如何？',
                    '还有多少订单待同步、待发货？',
                    '哪些订单金额异常偏高或偏低？',
                    '给我一份平台销售结构分析',
                ],
            ],
            'viewer' => [
                'label' => '只读访客',
                'icon' => 'fa-eye',
                'focus' => '只读查询与经营概览，不做任何业务判断与写操作建议',
                'data' => '销售订单、采购订单、生产工单、库存余额、检验记录的统计概览',
                'tools' => [
                    ['id' => 'inventory', 'name' => '库存查询', 'icon' => 'fa-boxes-stacked'],
                    ['id' => 'sales', 'name' => '销售概览', 'icon' => 'fa-chart-line'],
                ],
                'prompts' => [
                    '本月经营概览是怎样的？',
                    '各个模块的单据数量是多少？',
                    '库存总量与物料种类有多少？',
                ],
            ],
        ];
    }

    private static function buildPrompt(array $skill, string $name): string
    {
        return "你是电子工厂 ERP 系统的智能助手，正在为【{$skill['label']}】{$name} 服务。\n"
            . "职责范围：{$skill['focus']}。\n"
            . "可访问的数据：{$skill['data']}。\n"
            . "回答要求：\n"
            . "1. 用简体中文回答，结论先行，先给数字再给解释；\n"
            . "2. 需要数据时先调用工具查询，所有数字必须来自工具返回结果，严禁编造；\n"
            . "3. 超出该角色职责范围的问题，说明原因并给出建议找谁处理；\n"
            . "4. 给出 1-3 条可立即执行的建议，并标注风险点。";
    }
}
