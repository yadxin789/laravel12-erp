<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('category', 50)->default('system');
            $table->text('value')->nullable();
            $table->string('value_type', 20)->default('string');
            $table->string('label', 100)->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('editable')->default(true);
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'key']);
            $table->index(['category', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
