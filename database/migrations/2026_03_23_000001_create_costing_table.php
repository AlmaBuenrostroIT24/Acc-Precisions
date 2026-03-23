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
        Schema::create('costings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_schedule_id');
            $table->string('status', 50)->default('draft');
            $table->string('drawing_pdf_path')->nullable();
            $table->string('quote_pdf_path')->nullable();
            $table->string('type_material')->nullable();
            $table->decimal('qty_material', 10, 2)->default(0);
            $table->decimal('price_material', 10, 2)->default(0);
            $table->decimal('total_material', 12, 2)->default(0);
            $table->decimal('total_time_order', 12, 2)->default(0);
            $table->decimal('total_labor', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->decimal('grandtotal_cost', 12, 2)->default(0);
            $table->decimal('difference_cost', 12, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_schedule_id')
                ->references('id')
                ->on('orders_schedule')
                ->onDelete('cascade');

            $table->unique('order_schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costings');
    }
};
