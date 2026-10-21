<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receipt_id');
            $table->integer('line_number')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('inspected_quantity', 18, 4)->default(0);
            $table->decimal('qualified_quantity', 18, 4)->default(0);
            $table->decimal('rejected_quantity', 18, 4)->default(0);
            $table->unsignedBigInteger('purchase_order_line_id')->nullable();
            $table->string('batch_number', 50)->nullable();
            $table->date('production_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('quality_status', 30)->default('pending');
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->foreign('receipt_id')->references('id')->on('receipts');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('purchase_order_line_id')->references('id')->on('purchase_order_lines')->nullOnDelete();
            $table->unique(['receipt_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_lines');
    }
};
