<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('orders_schedule', 'status_at_endate_mach')) {
            return;
        }

        Schema::table('orders_schedule', function (Blueprint $table) {
            $table->string('status_at_endate_mach')->nullable()->after('endate_mach');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('orders_schedule', 'status_at_endate_mach')) {
            return;
        }

        Schema::table('orders_schedule', function (Blueprint $table) {
            $table->dropColumn('status_at_endate_mach');
        });
    }
};

