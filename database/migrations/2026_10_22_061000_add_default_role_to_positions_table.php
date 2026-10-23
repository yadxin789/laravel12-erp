<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 岗位 ↔ 角色关联：
     * - positions.default_role   任职该岗位时默认授予的系统角色（权限载体）
     * - employees.department_id  所属部门（组织主数据外键）
     * - employees.position_id    任职岗位（组织主数据外键）
     *
     * 部门/岗位只描述组织编制，授权仍由角色完成；
     * 员工任职岗位后，系统按 default_role 自动为其关联账号授予角色。
     */
    public function up(): void
    {
        if (! Schema::hasColumn('positions', 'default_role')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->string('default_role', 50)->nullable()->after('headcount');
            });
        }

        if (! Schema::hasColumn('employees', 'department_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('department');
                $table->unsignedBigInteger('position_id')->nullable()->after('position');
                $table->index('department_id', 'employees_department_id_index');
                $table->index('position_id', 'employees_position_id_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('positions', 'default_role')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->dropColumn('default_role');
            });
        }

        if (Schema::hasColumn('employees', 'position_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropIndex('employees_department_id_index');
                $table->dropIndex('employees_position_id_index');
                $table->dropColumn(['department_id', 'position_id']);
            });
        }
    }
};
