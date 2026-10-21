<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_receipt_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_receipt_id');
            $table->integer('line_number')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->string('batch_number', 50)->nullable();
            $table->date('production_date')->nullable();
            $table->string('quality_status', 30)->default('pending');
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->foreign('production_receipt_id')->references('id')->on('production_receipts');
            $table->foreign('item_id')->references('id')->on('items');
            $table->unique(['production_receipt_id', 'line_number'], 'production_receipt_lines_production_receipt_id_line_num_fc08cbb2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_receipt_lines');
    }
};
