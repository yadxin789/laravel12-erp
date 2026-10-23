<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('spec_sheet_path', 500)
                ->nullable()
                ->default(null)
                ->after('image_path')
                ->comment('规格书（PDF）存储路径，默认为空');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('spec_sheet_path');
        });
    }
};
