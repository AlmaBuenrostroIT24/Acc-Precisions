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
        Schema::create('qa_faisummary', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('num_operation');
            $table->enum('insp_type', ['FAI', 'IPI']);
            $table->string('operation');
            $table->string('operator');
            $table->enum('results', ['pass', 'no pass']);
            $table->string('sb_is')->nullable();
            $table->text('observation')->nullable();
            $table->string('station');
            $table->string('method')->nullable();
            $table->string('inspector');
            $table->string('part_rev');
            $table->string('job');
    
            // ✅ Campo adicional
            $table->enum('status_operation', ['pending', 'in progress', 'completed'])->default('pending');
    
            $table->unsignedBigInteger('order_schedule_id')->nullable();
            $table->foreign('order_schedule_id')->references('id')->on('orders_schedule')->onDelete('set null');//Si se elimina una orden, las filas no se borran, solo se desvinculan (order_schedule_id = null).
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qa_faisummary'); //si haces un rollback (php artisan migrate:rollback), debe eliminar la tabla qa_faisummary.
    }
};