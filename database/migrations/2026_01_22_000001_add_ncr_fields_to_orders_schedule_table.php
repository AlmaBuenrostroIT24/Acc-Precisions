<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders_schedule', 'ncr_number')) {
            return;
        }

        Schema::table('orders_schedule', function (Blueprint $table) {
            $table->string('ncr_number', 50)->nullable()->after('status');
            $table->text('ncr_notes')->nullable()->after('ncr_number');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('orders_schedule', 'ncr_number')) {
            return;
        }

        Schema::table('orders_schedule', function (Blueprint $table) {
            $table->dropColumn(['ncr_number', 'ncr_notes']);
        });
    }
};

