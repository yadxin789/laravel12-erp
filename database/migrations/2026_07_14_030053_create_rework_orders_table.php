<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rework_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 50)->unique();
            $table->unsignedBigInteger('production_order_id')->nullable();
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('completed_quantity', 18, 4)->default(0);
            $table->string('status', 30)->default('draft');
            $table->text('reason')->nullable();
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('production_orders')->nullOnDelete();
            $table->foreign('item_id')->references('id')->on('items');
            $table->index(['order_no']);
            $table->index(['production_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rework_orders');
    }
};
