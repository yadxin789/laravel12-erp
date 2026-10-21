<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('supplier_id');
            $table->decimal('unit_price', 18, 4)->default(0);
            $table->string('currency', 10)->default('CNY');
            $table->decimal('lead_time', 10, 2)->default(0);
            $table->boolean('primary')->default(false);
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->unique(['item_id', 'supplier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_suppliers');
    }
};
