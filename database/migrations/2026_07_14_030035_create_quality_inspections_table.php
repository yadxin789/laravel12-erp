<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_no', 50)->unique();
            $table->string('inspection_type', 30);
            $table->unsignedBigInteger('source_document_id')->nullable();
            $table->string('source_document_type', 50)->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('batch_number', 50)->nullable();
            $table->date('inspection_date');
            $table->decimal('sample_size', 18, 4)->default(0);
            $table->decimal('inspected_size', 18, 4)->default(0);
            $table->decimal('qualified_size', 18, 4)->default(0);
            $table->decimal('rejected_size', 18, 4)->default(0);
            $table->string('result', 30)->default('pending');
            $table->string('status', 30)->default('pending');
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items')->nullOnDelete();
            $table->index(['inspection_no']);
            $table->index(['inspection_type', 'status']);
            $table->index(['source_document_type', 'source_document_id'], 'quality_inspections_source_document_type_source_documen_dca87f1b');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_inspections');
    }
};
