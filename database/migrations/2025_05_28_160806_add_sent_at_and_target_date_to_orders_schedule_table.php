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
            if (!Schema::hasColumn('orders_schedule', 'target_date')) {
                $table->integer('target_date')->nullable(); // o el tipo correcto
            } else {
                $table->integer('target_date')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders_schedule', function (Blueprint $table) {
            $table->dropColumn(['sent_at', 'target_date']);
        });
    }
};
