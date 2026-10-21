<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_order_id');
            $table->integer('line_number')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('delivered_quantity', 18, 4)->default(0);
            $table->decimal('unit_price', 18, 4)->default(0);
            $table->decimal('tax_rate', 10, 2)->default(0);
            $table->decimal('line_amount', 18, 4)->default(0);
            $table->decimal('line_tax', 18, 4)->default(0);
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->foreign('sales_order_id')->references('id')->on('sales_orders');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
            $table->unique(['sales_order_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_lines');
    }
};
