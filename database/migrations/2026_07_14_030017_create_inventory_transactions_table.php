<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no', 50);
            $table->string('transaction_type', 50);
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('batch_number', 50)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->decimal('quantity', 18, 4)->default(0);
            $table->string('quality_status', 30)->default('qualified');
            $table->unsignedBigInteger('source_document_id')->nullable();
            $table->string('source_document_type', 50)->nullable();
            $table->unsignedBigInteger('source_document_line_id')->nullable();
            $table->unsignedBigInteger('related_transaction_id')->nullable();
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->foreign('location_id')->references('id')->on('locations')->nullOnDelete();
            $table->foreign('related_transaction_id')->references('id')->on('inventory_transactions')->nullOnDelete();
            $table->index(['transaction_no']);
            $table->index(['transaction_type']);
            $table->index(['item_id', 'warehouse_id']);
            $table->index(['source_document_type', 'source_document_id'], 'inventory_transactions_source_document_type_source_docu_67bdef76');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
