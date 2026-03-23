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
        Schema::create('costing_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('costing_id')->nullable();
            $table->unsignedBigInteger('costing_operation_id')->nullable();
            $table->string('action', 50);
            $table->string('field_changed', 100)->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('costing_id')
                ->references('id')
                ->on('costings')
                ->onDelete('cascade');

            $table->foreign('costing_operation_id')
                ->references('id')
                ->on('costing_operations')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costing_logs');
    }
};
