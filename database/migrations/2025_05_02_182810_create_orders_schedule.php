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
        Schema::create('orders_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_id');
            $table->string('PN'); // Número de parte
            $table->string('Part_description'); // Parte/Descripción
            $table->string('costumer'); // Cliente
            $table->integer('qty'); // Cantidad
            $table->string('operation'); // Cuantas operaciones tiene
            $table->string('machines'); //Machine donde se corre
            $table->boolean('done')->default(false); // En cuantos dias se va a correr
            $table->string('status')->nullable(); // En que status esta (onrack, on hold  )
            $table->date('machining_date')->nullable(); // Fecha limite de maquinado
            $table->date('due_date')->nullable(); //Fecha limite para enviar la orden
            $table->integer('days')->nullable(); // Cuantos dias faltan para que se venza la orden
            $table->boolean('alert')->default(false); // color del limite de dias que vence la orden
            $table->text('report')->nullable(); // Tiene reporte
            $table->string('our_source')->nullable(); // lleva proceso fuera
            $table->text('station_notes')->nullable(); // Observaciones
            $table->enum('location', ['Yarnell', 'Hearst'])->nullable();
    
            // Campos adicionales sugeridos
            $table->string('priority')->default('Media'); // Alta, Media, Baja
            $table->unsignedBigInteger('assigned_to')->nullable(); // ID del operador (relación opcional)
            $table->string('material_type')->nullable(); //Tipo de material que se esta corriendo
            $table->integer('process_time')->nullable(); // en minutos o horas
            $table->unsignedBigInteger('created_by')->nullable(); // ID del usuario que creó la orden
            $table->boolean('canceled')->default(false); // Orden Cancelada
            $table->string('tracking_number')->nullable(); // Numero de guia cuando se envia la orden
            $table->string('revision')->nullable(); //Versión del plano o pieza, si aplica.
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
