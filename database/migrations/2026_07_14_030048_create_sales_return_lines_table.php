<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_return_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_return_id');
            $table->integer('line_number')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->unsignedBigInteger('sales_order_line_id')->nullable();
            $table->string('batch_number', 50)->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->foreign('sales_return_id')->references('id')->on('sales_returns');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('sales_order_line_id')->references('id')->on('sales_order_lines')->nullOnDelete();
            $table->unique(['sales_return_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_lines');
    }
};
