<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 发票 OCR 识别结果
     *
     * OCR 走队列异步执行，必须有一张表承载「任务状态 + 结果」，
     * 否则前端无法回查（此前 ProcessInvoiceOcrJob 只在日志里打印结果）。
     */
    public function up(): void
    {
        Schema::create('ai_ocr_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('disk', 32)->default('local');
            $table->string('path')->nullable();          // 原始图片存储路径
            $table->string('original_name')->nullable();
            $table->string('status', 20)->default('pending'); // pending/processing/success/failed
            $table->json('result')->nullable();          // 结构化识别结果
            $table->text('raw_response')->nullable();    // 模型原始返回，便于排错
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_ocr_results');
    }
};
