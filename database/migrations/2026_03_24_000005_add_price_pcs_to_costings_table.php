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
        Schema::table('costings', function (Blueprint $table) {
            $table->decimal('price_pcs', 12, 2)
                ->default(0)
                ->after('sale_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('costings', function (Blueprint $table) {
            $table->dropColumn('price_pcs');
        });
    }
};
