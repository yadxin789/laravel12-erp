<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('prefix', 20)->nullable();
            $table->integer('number_length')->default(6);
            $table->integer('current_number')->default(1);
            $table->string('year_format', 10)->default('Y');
            $table->boolean('year_reset')->default(true);
            $table->boolean('month_reset')->default(false);
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['code', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('number_sequences');
    }
};
