<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 群聊（is_private=false 且 receiver_id=null）与私聊（is_private=true + receiver_id）共用一张表
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->foreign('receiver_id')->references('id')->on('users')->cascadeOnDelete();
            $table->boolean('is_private')->default(false)->comment('false=群聊 true=私聊');
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_private', 'created_at'], 'chat_messages_private_created_idx');
        });

        // 「清空我的聊天记录」只影响本人视图，不影响其他人
        Schema::create('chat_clear_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('chat_type', 20)->comment('group | private');
            $table->string('conversation_key', 64)->comment('group: group ; private: user_{id}');
            $table->foreignId('other_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('cleared_at');
            $table->timestamps();

            $table->unique(['user_id', 'chat_type', 'conversation_key'], 'chat_clear_unique_conversation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_clear_states');
        Schema::dropIfExists('chat_messages');
    }
};
