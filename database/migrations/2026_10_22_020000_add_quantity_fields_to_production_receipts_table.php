<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_receipts', function (Blueprint $table) {
            if (! Schema::hasColumn('production_receipts', 'quantity')) {
                $table->decimal('quantity', 18, 4)->default(0)->after('warehouse_id');
            }
            if (! Schema::hasColumn('production_receipts', 'batch_number')) {
                $table->string('batch_number', 50)->nullable()->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('production_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('production_receipts', 'batch_number')) {
                $table->dropColumn('batch_number');
            }
            if (Schema::hasColumn('production_receipts', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });
    }
};
