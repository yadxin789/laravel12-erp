<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('level', 30)->nullable();      // 高管/经理/主管/专员/员工
            $table->integer('headcount')->default(1);     // 编制人数
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'active']);
            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
