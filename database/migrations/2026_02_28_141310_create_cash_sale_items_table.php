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
        Schema::create('cash_sale_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cash_sale_id');
            $table->unsignedBigInteger('product_id');

            $table->integer('qty');
            $table->integer('bonus_qty')->default(0);

            $table->bigInteger('price');
            $table->bigInteger('subtotal')->default(0);

            $table->bigInteger('fee_nominal')->default(0);
            $table->decimal('hpp_snapshot', 15, 2)->default(0);

            $table->timestamps();

            // Foreign Keys
            $table->foreign('cash_sale_id')
                ->references('id')
                ->on('cash_sales')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');

            // Index
            $table->index(['cash_sale_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_sale_items');
    }
};