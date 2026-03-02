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
        Schema::create('sales_stock_session_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('product_id');

            // Stok awal saat session dimulai
            $table->integer('opening_qty')->default(0);

            // Hasil perhitungan sistem saat close
            $table->integer('system_remaining_qty')->nullable();

            // Input fisik dari gudang saat close
            $table->integer('physical_remaining_qty')->nullable();

            // Selisih (physical - system)
            $table->integer('difference_qty')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('session_id')
                  ->references('id')
                  ->on('sales_stock_sessions')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // 1 produk hanya boleh sekali dalam 1 session
            $table->unique(['session_id','product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_stock_session_items');
    }
};