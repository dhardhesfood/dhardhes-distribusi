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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();

            // nama bahan
            $table->string('name');

            // harga pembelian (opsional, untuk referensi)
            $table->decimal('price_per_unit', 12, 2)->nullable();

            // satuan pembelian (kg, liter, dll)
            $table->string('unit')->default('kg');

            // 🔥 harga utama untuk HPP (WAJIB)
            $table->decimal('price_per_gram', 12, 4);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};