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
            $table->string('co')->nullable()->after('work_id'); // Ajusta 'after' según tu estructura
            $table->string('cust_po')->nullable()->after('co');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders_schedule', function (Blueprint $table) {
            $table->dropColumn(['co', 'cust_po']);
        });
    }
};
