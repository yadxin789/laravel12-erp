<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->string('spec', 200)->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('purchase_unit_id')->nullable();
            $table->decimal('purchase_conversion_rate', 18, 6)->default(1.0);
            $table->string('item_type', 30)->default('raw_material');
            $table->boolean('batch_management')->default(false);
            $table->boolean('serial_management')->default(false);
            $table->boolean('quality_inspection_required')->default(false);
            $table->decimal('safety_stock', 18, 4)->default(0);
            $table->decimal('min_order_quantity', 18, 4)->default(1);
            $table->decimal('standard_cost', 18, 4)->default(0);
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('item_categories')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('purchase_unit_id')->references('id')->on('units')->nullOnDelete();
            $table->index(['code', 'active']);
            $table->index(['category_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
