<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 数据库备份台账
     *
     * 「备份有没有做成功」本身必须可监控，因此每次备份都留痕，
     * 供 erp:health 检查最近一次备份是否过期或失败。
     */
    public function up(): void
    {
        Schema::create('database_backups', function (Blueprint $table) {
            $table->id();
            $table->string('connection', 64)->nullable(); // 数据库连接名
            $table->string('database', 128)->nullable();  // 库名
            $table->string('disk', 32)->default('local');
            $table->string('filename');
            $table->string('path');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('status', 16)->default('running'); // running / success / failed
            $table->boolean('compressed')->default(false);
            $table->unsignedInteger('duration_ms')->default(0);
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_backups');
    }
};
