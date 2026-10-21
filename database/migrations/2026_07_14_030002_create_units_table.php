<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 50);
            $table->string('short_name', 20)->nullable();
            $table->decimal('conversion_rate', 18, 6)->default(1.0);
            $table->unsignedBigInteger('base_unit_id')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('base_unit_id')->references('id')->on('units')->nullOnDelete();
            $table->index(['code', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
