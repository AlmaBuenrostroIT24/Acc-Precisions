<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders_schedule', function (Blueprint $table) {
            if (!Schema::hasColumn('orders_schedule', 'ncr_reviewer')) {
                $table->string('ncr_reviewer', 120)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders_schedule', function (Blueprint $table) {
            if (Schema::hasColumn('orders_schedule', 'ncr_reviewer')) {
                $table->dropColumn('ncr_reviewer');
            }
        });
    }
};

