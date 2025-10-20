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
        Schema::create('qa_samplingplans', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('min_qty');
            $table->unsignedInteger('max_qty')->nullable(); // null = sin límite superior
            $table->decimal('normal_qty', 6, 2); // Puede ser número o porcentaje
            $table->decimal('tightened_qty', 6, 2);
            $table->decimal('surface_qty', 6, 2);
            $table->boolean('is_percent')->default(false); // true si es % en vez de cantidad
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qa_samplingplans');
    }
};
