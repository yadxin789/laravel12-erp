<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 审批流配置
        Schema::create('approval_flows', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('document_type', 50); // sales_order/purchase_order/requisition/quality
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        // 审批流节点
        Schema::create('approval_flow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_flow_id')->constrained('approval_flows')->cascadeOnDelete();
            $table->integer('step_order'); // 1,2,3...
            $table->string('name', 100); // 如：部门经理审批、财务审批
            $table->string('approver_type', 30); // role/user
            $table->string('approver_value'); // role name 或 user_id
            $table->timestamps();
        });

        // 运行时审批任务
        Schema::create('approval_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 50);
            $table->unsignedBigInteger('document_id');
            $table->integer('step_order');
            $table->string('step_name', 100);
            $table->foreignId('approver_id')->constrained('users');
            $table->string('status', 20)->default('pending'); // pending/approved/rejected
            $table->text('comment')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // 插入默认审批流：销售订单两级审批
        \DB::table('approval_flows')->insert([
            ['name' => '销售订单审批流', 'document_type' => 'sales_order', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '采购订单审批流', 'document_type' => 'purchase_order', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        \DB::table('approval_flow_steps')->insert([
            ['approval_flow_id' => 1, 'step_order' => 1, 'name' => '销售经理审批', 'approver_type' => 'role', 'approver_value' => 'sales_manager', 'created_at' => now(), 'updated_at' => now()],
            ['approval_flow_id' => 1, 'step_order' => 2, 'name' => '总经理审批', 'approver_type' => 'role', 'approver_value' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['approval_flow_id' => 2, 'step_order' => 1, 'name' => '采购经理审批', 'approver_type' => 'role', 'approver_value' => 'purchase_manager', 'created_at' => now(), 'updated_at' => now()],
            ['approval_flow_id' => 2, 'step_order' => 2, 'name' => '财务审批', 'approver_type' => 'role', 'approver_value' => 'admin', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_tasks');
        Schema::dropIfExists('approval_flow_steps');
        Schema::dropIfExists('approval_flows');
    }
};
