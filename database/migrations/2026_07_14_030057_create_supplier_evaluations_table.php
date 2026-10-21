<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('evaluation_no', 50)->unique();
            $table->unsignedBigInteger('supplier_id');
            $table->date('evaluation_date');
            $table->decimal('quality_score', 5, 2)->default(0);
            $table->decimal('delivery_score', 5, 2)->default(0);
            $table->decimal('service_score', 5, 2)->default(0);
            $table->decimal('price_score', 5, 2)->default(0);
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->index(['evaluation_no']);
            $table->index(['supplier_id', 'evaluation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_evaluations');
    }
};
