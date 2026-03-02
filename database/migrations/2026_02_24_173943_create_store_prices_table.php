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
        Schema::create('store_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                  ->constrained('stores')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->integer('price');
            // harga konsinyasi per pcs untuk toko tersebut

            $table->timestamps();

            $table->unique(['store_id', 'product_id']);
            // satu produk hanya boleh satu harga per toko
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_prices');
    }
};
