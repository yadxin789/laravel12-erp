<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->integer('line_number')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->unsignedBigInteger('sales_order_line_id')->nullable();
            $table->string('batch_number', 50)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->foreign('shipment_id')->references('id')->on('shipments');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('sales_order_line_id')->references('id')->on('sales_order_lines')->nullOnDelete();
            $table->unique(['shipment_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_lines');
    }
};
