<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 多供应商：可同时配置多家，每家独立保存密钥
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique()->comment('供应商标识：deepseek/qwen/doubao/hunyuan');
            $table->string('name', 100);
            $table->string('base_url', 500)->nullable();
            $table->text('api_key')->nullable();
            $table->boolean('enabled')->default(true)->comment('是否启用该供应商');
            $table->boolean('is_default')->default(false)->comment('是否默认供应商');
            $table->string('note', 500)->nullable();
            $table->timestamps();
        });

        // 模型目录：每个供应商下可有多个对话/视觉模型
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('ai_providers')->cascadeOnDelete();
            $table->string('model_id', 120)->comment('调用标识，如 deepseek-chat');
            $table->string('name', 150)->comment('展示名称');
            $table->boolean('is_vision')->default(false)->comment('是否支持图片理解');
            $table->boolean('enabled')->default(true)->comment('是否开放给用户选择');
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['provider_id', 'model_id'], 'ai_models_provider_model_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_models');
        Schema::dropIfExists('ai_providers');
    }
};
