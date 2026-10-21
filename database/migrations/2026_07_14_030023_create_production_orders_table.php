<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 50)->unique();
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('completed_quantity', 18, 4)->default(0);
            $table->decimal('scrap_quantity', 18, 4)->default(0);
            $table->string('status', 30)->default('draft');
            $table->unsignedBigInteger('bom_header_id')->nullable();
            $table->string('bom_version', 20)->nullable();
            $table->unsignedBigInteger('routing_header_id')->nullable();
            $table->string('routing_version', 20)->nullable();
            $table->unsignedBigInteger('work_center_id')->nullable();
            $table->unsignedBigInteger('production_line_id')->nullable();
            $table->date('plan_start_date')->nullable();
            $table->date('plan_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('planned_cost', 18, 4)->default(0);
            $table->decimal('actual_cost', 18, 4)->default(0);
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('bom_header_id')->references('id')->on('bom_headers')->nullOnDelete();
            $table->foreign('routing_header_id')->references('id')->on('routing_headers')->nullOnDelete();
            $table->foreign('work_center_id')->references('id')->on('work_centers')->nullOnDelete();
            $table->foreign('production_line_id')->references('id')->on('production_lines')->nullOnDelete();
            $table->index(['order_no']);
            $table->index(['item_id', 'status']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
