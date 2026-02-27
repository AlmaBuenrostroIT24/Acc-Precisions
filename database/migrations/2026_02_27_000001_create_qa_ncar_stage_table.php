<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('qa_ncar_stage')) {
            return;
        }

        Schema::create('qa_ncar_stage', function (Blueprint $table) {
            $table->id();
            // Match qa_ncartype.id type (many installs use INT UNSIGNED created manually in phpMyAdmin)
            $table->unsignedInteger('ncartype_id')->index();
            $table->string('stage', 120);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->foreign('ncartype_id')
                ->references('id')
                ->on('qa_ncartype')
                ->onDelete('cascade');

            $table->unique(['ncartype_id', 'stage'], 'qa_ncar_stage_type_stage_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_ncar_stage');
    }
};
