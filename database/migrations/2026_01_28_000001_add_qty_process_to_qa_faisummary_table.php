<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('qa_faisummary')) {
            return;
        }

        if (!Schema::hasColumn('qa_faisummary', 'qty_process')) {
            $hasQtyPcs = Schema::hasColumn('qa_faisummary', 'qty_pcs');
            Schema::table('qa_faisummary', function (Blueprint $table) {
                $col = $table->unsignedInteger('qty_process')->nullable();
                if ($hasQtyPcs) {
                    $col->after('qty_pcs');
                }
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('qa_faisummary')) {
            return;
        }

        if (Schema::hasColumn('qa_faisummary', 'qty_process')) {
            Schema::table('qa_faisummary', function (Blueprint $table) {
                $table->dropColumn('qty_process');
            });
        }
    }
};
