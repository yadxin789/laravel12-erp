<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== P3-1 HR ==========
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('emp_no', 50)->unique();
            $table->string('name', 100);
            $table->string('department', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->date('hire_date')->nullable();
            $table->date('leave_date')->nullable();
            $table->string('status', 20)->default('active'); // active/left
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->date('date');
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->string('status', 20)->default('normal'); // normal/late/early/absent/leave
            $table->text('remark')->nullable();
            $table->timestamps();
        });

        // ========== P3-2 项目管理 ==========
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('planning'); // planning/in_progress/on_hold/completed/cancelled
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 18, 4)->default(0);
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('todo'); // todo/in_progress/done
            $table->integer('priority')->default(2); // 1高 2中 3低
            $table->date('due_date')->nullable();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->date('date');
            $table->decimal('hours', 5, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // ========== P3-3 固定资产 ==========
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_no', 50)->unique();
            $table->string('name', 200);
            $table->string('category', 100)->nullable(); // 设备/车辆/办公
            $table->string('location')->nullable();
            $table->decimal('original_cost', 18, 4);
            $table->integer('useful_life_years')->default(5);
            $table->date('purchase_date');
            $table->date('start_depreciation_date')->nullable();
            $table->string('status', 20)->default('in_use'); // in_use/idle/repair/scrapped
            $table->foreignId('assigned_to')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
        });

        // ========== P3-4 电商对接 ==========
        Schema::create('ecommerce_orders', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 50); // taobao/jd/amazon/pdd
            $table->string('platform_order_no', 100);
            $table->string('buyer_name')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->text('shipping_address')->nullable();
            $table->decimal('total_amount', 18, 4);
            $table->string('status', 30)->default('pending'); // pending/synced/shipped/completed/cancelled
            $table->json('items')->nullable(); // 订单明细
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_orders');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('employees');
    }
};
