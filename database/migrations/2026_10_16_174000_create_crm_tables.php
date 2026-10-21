<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CRM 线索
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200); // 联系人/公司名
            $table->string('contact_person')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->string('source', 50)->default('manual'); // manual/website/exhibition/referral/cold_call
            $table->string('status', 30)->default('new'); // new/contacted/qualified/opportunity/lost/converted
            $table->text('remark')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // CRM 商机
        Schema::create('crm_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200); // 商机名称
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->decimal('expected_amount', 18, 4)->default(0);
            $table->integer('probability')->default(50); // 0-100%
            $table->date('expected_close_date')->nullable();
            $table->string('stage', 30)->default('prospecting'); // prospecting/qualification/Proposal/negotiation/closed_won/closed_lost
            $table->text('remark')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 跟进记录
        Schema::create('crm_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->cascadeOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained('crm_opportunities')->cascadeOnDelete();
            $table->string('type', 30)->default('note'); // note/call/email/meeting
            $table->text('content');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_follow_ups');
        Schema::dropIfExists('crm_opportunities');
        Schema::dropIfExists('crm_leads');
    }
};
