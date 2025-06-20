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
        Schema::table('orders_schedule', function (Blueprint $table) {
            $table->date('endate_mach')->nullable()->after('target_date'); // reemplaza 'algún_campo_existente' si quieres posicionarlo
            $table->date('target_mach')->nullable()->after('endate_mach');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders_schedule', function (Blueprint $table) {
            $table->dropColumn(['endate_mach', 'target_mach']);
        });
    }
};
