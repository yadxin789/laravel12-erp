<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_order_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_order_id');
            $table->integer('line_number')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->decimal('required_quantity', 18, 4)->default(0);
            $table->decimal('issued_quantity', 18, 4)->default(0);
            $table->decimal('returned_quantity', 18, 4)->default(0);
            $table->decimal('scrap_quantity', 18, 4)->default(0);
            $table->string('batch_number', 50)->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('production_orders');
            $table->foreign('item_id')->references('id')->on('items');
            $table->unique(['production_order_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_lines');
    }
};
