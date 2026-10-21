<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_no', 50)->unique();
            $table->unsignedBigInteger('production_order_id');
            $table->unsignedBigInteger('operation_id')->nullable();
            $table->date('report_date');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('scrap_quantity', 18, 4)->default(0);
            $table->decimal('rework_quantity', 18, 4)->default(0);
            $table->text('remark')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('production_orders');
            $table->index(['report_no']);
            $table->index(['production_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_reports');
    }
};
