<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 系统告警表
     *
     * 用于告警去重、冷却（抑制重复刷屏）与恢复检测，等价于
     * Prometheus Alertmanager 的 firing / resolved 语义。
     */
    public function up(): void
    {
        Schema::create('system_alerts', function (Blueprint $table) {
            $table->id();
            // 同类告警指纹：source + 业务对象，保证同一问题只产生一条 firing 记录
            $table->string('fingerprint', 190)->unique();
            $table->string('source', 64);                 // queue / database / disk / backup / stock / logistics
            $table->string('level', 16)->default('warning'); // critical / warning / info
            $table->string('title');
            $table->text('message');
            $table->json('context')->nullable();
            $table->string('status', 16)->default('firing'); // firing / resolved
            $table->unsignedInteger('occurrences')->default(1);
            $table->unsignedInteger('notify_count')->default(0);
            $table->timestamp('first_triggered_at')->nullable();
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'level']);
            $table->index(['source', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_alerts');
    }
};
