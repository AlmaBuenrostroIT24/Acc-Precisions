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
        Schema::create('ord_machiningdatelogs', function (Blueprint $table) {
            $table->id();
    
            // Primero creas la columna
            $table->unsignedBigInteger('order_schedule_id');
    
            // Luego defines la relación
            $table->foreign('order_schedule_id')
                  ->references('id')
                  ->on('orders_schedule')
                  ->onDelete('cascade');
    
            $table->date('previous_date')->nullable();
            $table->date('new_date');
            $table->timestamps(); // esto crea created_at y updated_at
            $table->string('changed_by')->nullable(); // opcional, si usas Auth
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ord_machiningdatelogs');
    }
};
