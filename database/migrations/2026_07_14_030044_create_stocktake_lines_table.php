<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocktake_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stocktake_id');
            $table->integer('line_number')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->string('batch_number', 50)->nullable();
            $table->decimal('system_quantity', 18, 4)->default(0);
            $table->decimal('actual_quantity', 18, 4)->default(0);
            $table->decimal('difference', 18, 4)->default(0);
            $table->string('status', 30)->default('pending');
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->foreign('stocktake_id')->references('id')->on('stocktakes');
            $table->foreign('item_id')->references('id')->on('items');
            $table->unique(['stocktake_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocktake_lines');
    }
};
