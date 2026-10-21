<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requisition_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_requisition_id');
            $table->integer('line_number')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('ordered_quantity', 18, 4)->default(0);
            $table->date('required_date')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->foreign('purchase_requisition_id')->references('id')->on('purchase_requisitions');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            $table->unique(['purchase_requisition_id', 'line_number'], 'purchase_requisition_lines_purchase_requisition_id_line_7274db87');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requisition_lines');
    }
};
