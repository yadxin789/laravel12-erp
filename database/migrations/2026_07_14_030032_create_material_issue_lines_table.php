<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_issue_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_issue_id');
            $table->integer('line_number')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->unsignedBigInteger('production_order_line_id')->nullable();
            $table->string('batch_number', 50)->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->foreign('material_issue_id')->references('id')->on('material_issues');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('production_order_line_id')->references('id')->on('production_order_lines')->nullOnDelete();
            $table->unique(['material_issue_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_issue_lines');
    }
};
