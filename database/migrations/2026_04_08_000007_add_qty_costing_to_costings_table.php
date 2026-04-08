<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('costings', function (Blueprint $table) {
            $table->integer('qty_costing')->default(0)->after('type_material');
        });
    }

    public function down(): void
    {
        Schema::table('costings', function (Blueprint $table) {
            $table->dropColumn('qty_costing');
        });
    }
};
