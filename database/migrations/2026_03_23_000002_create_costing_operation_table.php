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
        Schema::create('costing_operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('costing_id');
            $table->string('status', 50)->default('active');
            $table->string('name_operation');
            $table->string('resource_name')->nullable();
            $table->decimal('time_programming', 10, 2)->default(0);
            $table->decimal('time_setup', 10, 2)->default(0);
            $table->decimal('runtime_pcs', 10, 4)->default(0);
            $table->decimal('runtime_total', 10, 2)->default(0);
            $table->decimal('total_time_operation', 10, 2)->default(0);
            $table->decimal('labor_rate', 10, 2)->default(0);
            $table->decimal('operation_cost', 12, 2)->default(0);
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('costing_id')
                ->references('id')
                ->on('costings')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costing_operations');
    }
};
