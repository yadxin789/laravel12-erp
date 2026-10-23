<?php

namespace Database\Seeders;

use App\Models\HR\Employee;
use App\Models\HR\JobApplication;
use App\Models\HR\PerformanceReview;
use App\Models\HR\RecruitmentRequest;
use App\Models\HR\SalaryRecord;
use App\Models\MasterData\Department;
use App\Models\MasterData\Position;
use Illuminate\Database\Seeder;

/**
 * HR 扩展模块演示数据：薪资 / 绩效 / 招聘
 *
 * 依赖：部门岗位主数据（DepartmentPositionSeeder）、员工档案。
 * 幂等：按唯一键 updateOrCreate，可重复执行。
 */
class HrExtDataSeeder extends Seeder
{
    public function run(): void
    {
        // 历史员工数据只有部门/岗位文本，按名称回填组织主数据外键，打通「岗位 → 角色」
        $this->linkLegacyOrg();

        $employees = Employee::where('status', 'active')->orderBy('emp_no')->limit(10)->get();

        if ($employees->isEmpty()) {
            return;
        }

        $this->seedSalaries($employees);
        $this->seedPerformances($employees);
        $this->seedRecruitment();
    }

    /**
     * 把员工表里的部门/岗位文本匹配到组织主数据外键。
     */
    private function linkLegacyOrg(): void
    {
        foreach (Employee::whereNull('position_id')->get() as $employee) {
            $position = $employee->position ? Position::where('name', $employee->position)->first() : null;
            $department = $employee->department ? Department::where('name', $employee->department)->first() : null;

            if ($position || $department) {
                $employee->update([
                    'position_id' => $position?->id,
                    'department_id' => $department?->id,
                ]);
            }
        }
    }

    /**
     * 近 3 个月工资单（含本月）
     */
    private function seedSalaries($employees): void
    {
        $months = [
            now()->subMonths(2)->format('Y-m'),
            now()->subMonths(1)->format('Y-m'),
            now()->format('Y-m'),
        ];

        // 按工号顺序给出稳定且有差异的薪资参数
        $plans = [
            ['base' => 18000, 'allowance' => 2000, 'bonus' => 3000],
            ['base' => 12000, 'allowance' => 1500, 'bonus' => 2000],
            ['base' => 9000,  'allowance' => 1000, 'bonus' => 1200],
            ['base' => 7500,  'allowance' => 800,  'bonus' => 900],
            ['base' => 6800,  'allowance' => 800,  'bonus' => 700],
            ['base' => 6200,  'allowance' => 600,  'bonus' => 600],
            ['base' => 5800,  'allowance' => 600,  'bonus' => 500],
            ['base' => 5500,  'allowance' => 500,  'bonus' => 400],
            ['base' => 5200,  'allowance' => 500,  'bonus' => 400],
            ['base' => 5000,  'allowance' => 400,  'bonus' => 300],
        ];

        foreach ($months as $mi => $period) {
            // 前两个月已发放，本月按草稿/已核准展示
            $status = $mi === 2 ? 'approved' : 'paid';
            $paidAt = $mi === 2 ? null : now()->subMonths(2 - $mi)->endOfMonth()->toDateString();

            foreach ($employees as $i => $employee) {
                $plan = $plans[$i % count($plans)];
                $overtime = ($i % 3 === 0) ? 800 : 0;
                $social = round($plan['base'] * 0.105, 2);
                $bonus = $plan['bonus'] + ($mi === 0 ? -400 : ($mi === 1 ? 200 : 0));
                $gross = $plan['base'] + $plan['allowance'] + $bonus + $overtime;
                $tax = (float) max(0, ($gross - $social - 5000) * 0.1);

                SalaryRecord::updateOrCreate(
                    ['employee_id' => $employee->id, 'period' => $period],
                    [
                        'base_salary' => $plan['base'],
                        'allowance' => $plan['allowance'],
                        'bonus' => $bonus,
                        'overtime_pay' => $overtime,
                        'social_insurance' => $social,
                        'tax' => round($tax, 2),
                        'other_deduction' => 0,
                        'net_pay' => round($gross - $social - round($tax, 2), 2),
                        'status' => $status,
                        'paid_at' => $paidAt,
                        'remark' => null,
                    ]
                );
            }
        }
    }

    /**
     * 本月 + 上月绩效评估
     */
    private function seedPerformances($employees): void
    {
        $scores = [92.5, 88.0, 81.5, 76.0, 84.5, 71.0, 90.0, 65.5, 79.0, 86.5];
        $summaries = [
            '目标达成情况良好，沟通协作主动',
            '执行力强，需加强跨部门协同',
            '交付质量稳定，建议提升主动性',
            '业务熟练，需改进时间管理',
            '客户反馈良好，可承担更多职责',
            '需加强专业知识学习',
            '带教新人表现突出',
            '出勤与交付需重点关注',
            '稳步提升，继续保持良好的交付节奏',
            '数据整理细致，建议提升汇报能力',
        ];

        foreach ([now()->subMonth(), now()] as $mi => $month) {
            $period = $month->format('Y-m');

            foreach ($employees as $i => $employee) {
                $score = $scores[$i % count($scores)] + ($mi === 0 ? -2 : 0);
                $score = max(60, min(100, $score));

                PerformanceReview::updateOrCreate(
                    ['employee_id' => $employee->id, 'period' => $period, 'type' => 'monthly'],
                    [
                        'score' => $score,
                        'grade' => PerformanceReview::gradeOf($score),
                        'reviewer' => '直属上级',
                        'summary' => $summaries[$i % count($summaries)],
                        'improvement' => $score < 70 ? '需制定改进计划并每月复盘' : null,
                        'status' => $mi === 1 ? 'draft' : 'completed',
                        'reviewed_at' => $month->endOfMonth()->toDateString(),
                    ]
                );
            }
        }
    }

    /**
     * 招聘需求 + 应聘者
     */
    private function seedRecruitment(): void
    {
        $wh = Department::where('code', 'WH')->first();
        $prod = Department::where('code', 'PROD')->first();
        $sales = Department::where('code', 'SALES')->first();

        $keep = Position::where('code', 'WH_KEEP')->first();
        $operator = Position::where('code', 'PROD_OP')->first();
        $salesRep = Position::where('code', 'SALES_REP')->first();

        $reqs = [
            [
                'req_no' => 'REC0001',
                'title' => '仓管员 2 名（业务扩产）',
                'department_id' => $wh?->id,
                'position_id' => $keep?->id,
                'headcount' => 2,
                'reason' => '业务扩产',
                'salary_range_min' => 5000,
                'salary_range_max' => 7000,
                'status' => 'open',
            ],
            [
                'req_no' => 'REC0002',
                'title' => '生产操作工 4 名（离职补员）',
                'department_id' => $prod?->id,
                'position_id' => $operator?->id,
                'headcount' => 4,
                'reason' => '离职补员',
                'salary_range_min' => 5500,
                'salary_range_max' => 8000,
                'status' => 'open',
            ],
            [
                'req_no' => 'REC0003',
                'title' => '销售专员 1 名（新增编制）',
                'department_id' => $sales?->id,
                'position_id' => $salesRep?->id,
                'headcount' => 1,
                'reason' => '新增编制',
                'salary_range_min' => 6000,
                'salary_range_max' => 9000,
                'status' => 'filled',
            ],
        ];

        $created = [];
        foreach ($reqs as $r) {
            $created[$r['req_no']] = RecruitmentRequest::updateOrCreate(
                ['req_no' => $r['req_no']],
                $r + ['employment_type' => 'full_time', 'expect_date' => now()->addMonth()->toDateString()]
            );
        }

        $applications = [
            ['app_no' => 'APP0001', 'name' => '张明', 'phone' => '13800001001', 'req' => 'REC0001', 'pos' => $keep?->id, 'edu' => '大专', 'exp' => 3, 'expect' => 6500, 'source' => '招聘网站', 'status' => 'interview', 'eval' => '有仓储系统操作经验，稳定性较好'],
            ['app_no' => 'APP0002', 'name' => '李娜', 'phone' => '13800001002', 'req' => 'REC0001', 'pos' => $keep?->id, 'edu' => '本科', 'exp' => 1, 'expect' => 5800, 'source' => '内部推荐', 'status' => 'screening', 'eval' => null],
            ['app_no' => 'APP0003', 'name' => '王强', 'phone' => '13800001003', 'req' => 'REC0002', 'pos' => $operator?->id, 'edu' => '高中', 'exp' => 5, 'expect' => 7000, 'source' => '现场招聘会', 'status' => 'offered', 'eval' => '有装配经验，可立即到岗'],
            ['app_no' => 'APP0004', 'name' => '刘洋', 'phone' => '13800001004', 'req' => 'REC0002', 'pos' => $operator?->id, 'edu' => '中专', 'exp' => 2, 'expect' => 6000, 'source' => '招聘网站', 'status' => 'hired', 'eval' => '已确认入职'],
            ['app_no' => 'APP0005', 'name' => '陈静', 'phone' => '13800001005', 'req' => 'REC0002', 'pos' => $operator?->id, 'edu' => '高中', 'exp' => 0, 'expect' => 5200, 'source' => '校园招聘', 'status' => 'applied', 'eval' => null],
            ['app_no' => 'APP0006', 'name' => '赵磊', 'phone' => '13800001006', 'req' => 'REC0003', 'pos' => $salesRep?->id, 'edu' => '本科', 'exp' => 4, 'expect' => 8500, 'source' => '猎头', 'status' => 'hired', 'eval' => '客户资源丰富，已录用'],
            ['app_no' => 'APP0007', 'name' => '孙丽', 'phone' => '13800001007', 'req' => 'REC0003', 'pos' => $salesRep?->id, 'edu' => '大专', 'exp' => 2, 'expect' => 6500, 'source' => '招聘网站', 'status' => 'rejected', 'eval' => '期望薪资与岗位不匹配'],
            ['app_no' => 'APP0008', 'name' => '周涛', 'phone' => '13800001008', 'req' => 'REC0001', 'pos' => $keep?->id, 'edu' => '本科', 'exp' => 6, 'expect' => 7500, 'source' => '招聘网站', 'status' => 'applied', 'eval' => null],
        ];

        foreach ($applications as $a) {
            JobApplication::updateOrCreate(
                ['app_no' => $a['app_no']],
                [
                    'name' => $a['name'],
                    'phone' => $a['phone'],
                    'email' => null,
                    'position_id' => $a['pos'],
                    'recruitment_id' => $created[$a['req']]->id ?? null,
                    'education' => $a['edu'],
                    'experience_years' => $a['exp'],
                    'expect_salary' => $a['expect'],
                    'source' => $a['source'],
                    'status' => $a['status'],
                    'interview_at' => in_array($a['status'], ['interview', 'offered'], true)
                        ? now()->addDays(3)->setTime(10, 0) : null,
                    'hired_at' => $a['status'] === 'hired' ? now()->toDateString() : null,
                    'evaluation' => $a['eval'],
                ]
            );
        }
    }
}
