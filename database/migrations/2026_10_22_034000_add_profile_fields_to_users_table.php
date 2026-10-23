<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->comment('头像：上传文件名或 defaults/avatarN.svg')->after('name');
            $table->string('phone', 30)->nullable()->comment('联系电话')->after('email');
            $table->string('position', 100)->nullable()->comment('岗位/职位')->after('phone');
            $table->string('signature', 500)->nullable()->comment('个性签名/简介')->after('position');
            $table->timestamp('last_seen_at')->nullable()->comment('最近活跃时间（在线状态判定）')->after('signature');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'phone', 'position', 'signature', 'last_seen_at']);
        });
    }
};
