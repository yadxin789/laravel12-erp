<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 物流查询通道（聚合服务商）+ 快递公司目录，同一张表用 type 区分
        Schema::create('express_providers', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('channel')->comment('channel=查询通道 carrier=快递公司');
            $table->string('key', 40)->unique()->comment('标识：kuaidi100 / sf / zto ...');
            $table->string('name', 100);
            $table->string('code', 40)->nullable()->comment('快递公司编码（通道侧需要的编码）');
            $table->string('api_base', 500)->nullable();
            $table->text('app_key')->nullable();
            $table->text('app_secret')->nullable();
            $table->unsignedInteger('monthly_quota')->default(0)->comment('每月免费额度（次）');
            $table->boolean('enabled')->default(false);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('note', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('logistics_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_no', 64)->comment('运单号');
            $table->string('carrier_key', 40)->nullable()->comment('快递公司标识');
            $table->string('carrier_name', 100)->nullable();
            $table->string('status', 30)->default('created')
                ->comment('created/in_transit/delivering/signed/exception/returning/closed');
            $table->string('receiver_name', 100)->nullable();
            $table->string('receiver_phone', 30)->nullable();
            $table->string('receiver_address', 500)->nullable();
            $table->string('related_type', 60)->nullable()->comment('关联单据类型：sales_shipment 等');
            $table->unsignedBigInteger('related_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('notify_enabled')->default(true);
            $table->timestamp('last_traced_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();

            $table->index('tracking_no', 'logistics_shipments_tracking_idx');
            $table->index('status', 'logistics_shipments_status_idx');
        });

        Schema::create('logistics_traces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('logistics_shipments')->cascadeOnDelete();
            $table->timestamp('traced_at')->nullable()->comment('轨迹发生时间');
            $table->string('status', 60)->nullable();
            $table->text('description')->nullable();
            $table->string('location', 200)->nullable();
            $table->timestamps();

            $table->index('shipment_id', 'logistics_traces_shipment_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_traces');
        Schema::dropIfExists('logistics_shipments');
        Schema::dropIfExists('express_providers');
    }
};
