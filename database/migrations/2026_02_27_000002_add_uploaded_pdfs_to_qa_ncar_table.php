<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qa_ncar', function (Blueprint $table) {
            if (!Schema::hasColumn('qa_ncar', 'pdf_upload_path')) {
                $table->string('pdf_upload_path', 255)->nullable()->after('ref');
            }
            if (!Schema::hasColumn('qa_ncar', 'email_upload_path')) {
                $table->string('email_upload_path', 255)->nullable()->after('pdf_upload_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('qa_ncar', function (Blueprint $table) {
            if (Schema::hasColumn('qa_ncar', 'email_upload_path')) {
                $table->dropColumn('email_upload_path');
            }
            if (Schema::hasColumn('qa_ncar', 'pdf_upload_path')) {
                $table->dropColumn('pdf_upload_path');
            }
        });
    }
};

