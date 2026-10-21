<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bom_header_id');
            $table->integer('line_number')->default(1);
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('scrap_rate', 10, 4)->default(0);
            $table->decimal('net_quantity', 18, 4)->default(0);
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->foreign('bom_header_id')->references('id')->on('bom_headers');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->unique(['bom_header_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_lines');
    }
};
