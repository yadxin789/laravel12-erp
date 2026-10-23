<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 角色/权限的中文展示名：
     * - roles.display_name       岗位中文名（可在后台改，留空则用岗位字典默认值）
     * - permissions.group        权限所属模块分组
     * - permissions.display      权限中文名
     */
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'display_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('display_name', 100)->nullable()->after('name');
            });
        }

        if (! Schema::hasColumn('permissions', 'group')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('group', 50)->nullable()->after('name');
            });
        }

        if (! Schema::hasColumn('permissions', 'display')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('display', 100)->nullable()->after('group');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('roles', 'display_name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('display_name');
            });
        }

        if (Schema::hasColumn('permissions', 'group')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('group');
            });
        }

        if (Schema::hasColumn('permissions', 'display')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('display');
            });
        }
    }
};
