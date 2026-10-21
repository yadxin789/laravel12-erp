<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->default('deepseek'); // deepseek/qwen/doubao/hunyuan/openai
            $table->string('api_key', 500)->nullable();
            $table->string('base_url', 500)->nullable();
            $table->string('model', 100)->default('deepseek-chat');
            $table->boolean('vision_enabled')->default(false); // 是否启用视觉模型（OCR）
            $table->string('vision_model', 100)->nullable(); // 视觉模型名称
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->integer('max_tokens')->default(2000);
            $table->boolean('enabled')->default(false); // 总开关
            $table->timestamps();
        });

        // 插入默认配置（占位符）
        \DB::table('ai_settings')->insert([
            'provider' => 'deepseek',
            'api_key' => 'sk-xxxxxxxxxxxxxxxxxxxxxxxx',
            'base_url' => 'https://api.deepseek.com/v1',
            'model' => 'deepseek-chat',
            'vision_enabled' => false,
            'vision_model' => 'deepseek-vl',
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'enabled' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
