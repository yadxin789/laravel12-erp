<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 50)->unique();
            $table->unsignedBigInteger('customer_id');
            $table->date('order_date');
            $table->date('delivery_date');
            $table->string('status', 30)->default('draft');
            $table->decimal('total_amount', 18, 4)->default(0);
            $table->decimal('total_tax', 18, 4)->default(0);
            $table->decimal('total_net_amount', 18, 4)->default(0);
            $table->decimal('delivered_quantity', 18, 4)->default(0);
            $table->decimal('total_quantity', 18, 4)->default(0);
            $table->string('currency', 10)->default('CNY');
            $table->string('payment_terms', 50)->nullable();
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->index(['order_no']);
            $table->index(['customer_id', 'status']);
            $table->index(['order_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
