<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('costings', function (Blueprint $table) {
            $table->decimal('hrs_programming', 12, 4)->default(0)->after('total_time_order');
            $table->decimal('hrs_setup', 12, 4)->default(0)->after('hrs_programming');
            $table->decimal('hrs_runtime', 12, 4)->default(0)->after('hrs_setup');
            $table->decimal('hrs_runtimetotal', 12, 4)->default(0)->after('hrs_runtime');
            $table->decimal('hrs_actual', 12, 4)->default(0)->after('hrs_runtimetotal');
            $table->decimal('hrs_variance', 12, 4)->default(0)->after('hrs_actual');
        });
    }

    public function down(): void
    {
        Schema::table('costings', function (Blueprint $table) {
            $table->dropColumn([
                'hrs_programming',
                'hrs_setup',
                'hrs_runtime',
                'hrs_runtimetotal',
                'hrs_actual',
                'hrs_variance',
            ]);
        });
    }
};
