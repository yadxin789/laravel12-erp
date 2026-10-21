<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_no', 50)->unique();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->date('shipment_date');
            $table->unsignedBigInteger('warehouse_id');
            $table->string('status', 30)->default('pending');
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->nullOnDelete();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->index(['shipment_no']);
            $table->index(['customer_id', 'status']);
            $table->index(['sales_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
