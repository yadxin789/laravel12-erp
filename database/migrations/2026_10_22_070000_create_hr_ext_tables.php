<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HR 模块扩展表：薪资 / 绩效 / 招聘
     *
     * - hr_salaries             工资单（按月，含应发/扣款/实发与发放状态）
     * - hr_performances         绩效评估（按周期评分与等级）
     * - hr_recruitments         招聘需求（岗位缺编申请）
     * - hr_applications         应聘者（投递 → 面试 → 录用/淘汰）
     */
    public function up(): void
    {
        Schema::create('hr_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('period', 7)->comment('薪资月份 YYYY-MM');
            $table->decimal('base_salary', 12, 2)->default(0)->comment('基本工资');
            $table->decimal('allowance', 12, 2)->default(0)->comment('岗位/交通/餐补等津贴');
            $table->decimal('bonus', 12, 2)->default(0)->comment('绩效奖金');
            $table->decimal('overtime_pay', 12, 2)->default(0)->comment('加班费');
            $table->decimal('social_insurance', 12, 2)->default(0)->comment('社保公积金个人部分');
            $table->decimal('tax', 12, 2)->default(0)->comment('个人所得税');
            $table->decimal('other_deduction', 12, 2)->default(0)->comment('其他扣款');
            $table->decimal('net_pay', 12, 2)->default(0)->comment('实发工资');
            $table->string('status', 20)->default('draft')->comment('draft 草稿 / approved 已核准 / paid 已发放');
            $table->date('paid_at')->nullable()->comment('发放日期');
            $table->string('remark')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'period'], 'hr_salaries_employee_period_unique');
            $table->index('period', 'hr_salaries_period_index');
        });

        Schema::create('hr_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('period', 7)->comment('考核周期 YYYY-MM 或季度');
            $table->string('type', 20)->default('monthly')->comment('monthly 月度 / quarterly 季度 / annual 年度');
            $table->decimal('score', 5, 2)->default(0)->comment('考核得分 0-100');
            $table->string('grade', 5)->nullable()->comment('A 优秀 / B 良好 / C 合格 / D 待改进');
            $table->string('reviewer')->nullable()->comment('考核人');
            $table->text('summary')->nullable()->comment('考核评语');
            $table->text('improvement')->nullable()->comment('改进建议');
            $table->string('status', 20)->default('draft')->comment('draft 草稿 / completed 已完成');
            $table->date('reviewed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'period', 'type'], 'hr_performances_unique');
        });

        Schema::create('hr_recruitments', function (Blueprint $table) {
            $table->id();
            $table->string('req_no', 30)->comment('需求编号');
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('title')->comment('需求名称，如：装配工 3 名');
            $table->unsignedInteger('headcount')->default(1)->comment('需求人数');
            $table->string('reason', 50)->nullable()->comment('缺编原因：新增编制/离职补员/业务扩产');
            $table->string('employment_type', 20)->default('full_time')->comment('全职/兼职/实习');
            $table->decimal('salary_range_min', 12, 2)->nullable();
            $table->decimal('salary_range_max', 12, 2)->nullable();
            $table->date('expect_date')->nullable()->comment('期望到岗日期');
            $table->string('status', 20)->default('open')->comment('open 招聘中 / filled 已招满 / closed 已关闭');
            $table->string('remark')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('req_no', 'hr_recruitments_req_no_unique');
        });

        Schema::create('hr_applications', function (Blueprint $table) {
            $table->id();
            $table->string('app_no', 30)->comment('应聘编号');
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('recruitment_id')->nullable()->constrained('hr_recruitments')->nullOnDelete();
            $table->string('education', 20)->nullable()->comment('学历');
            $table->unsignedInteger('experience_years')->nullable()->comment('工作年限');
            $table->decimal('expect_salary', 12, 2)->nullable()->comment('期望月薪');
            $table->string('source', 30)->nullable()->comment('渠道：招聘网站/内部推荐/校园招聘/猎头');
            $table->string('status', 20)->default('applied')
                ->comment('applied 已投递 / screening 筛选中 / interview 面试中 / offered 已发offer / hired 已录用 / rejected 已淘汰');
            $table->dateTime('interview_at')->nullable()->comment('面试时间');
            $table->date('hired_at')->nullable()->comment('入职日期（录用后填写，可转员工档案）');
            $table->text('evaluation')->nullable()->comment('面试评价');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('app_no', 'hr_applications_app_no_unique');
            $table->index('status', 'hr_applications_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_applications');
        Schema::dropIfExists('hr_recruitments');
        Schema::dropIfExists('hr_performances');
        Schema::dropIfExists('hr_salaries');
    }
};
