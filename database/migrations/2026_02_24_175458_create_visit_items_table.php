<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visit_id')
                  ->constrained('visits')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            // stok menurut sistem saat sales datang
            $table->integer('system_stock');

            // stok fisik dihitung di toko
            $table->integer('physical_stock');

            // target stok baru setelah kunjungan
            $table->integer('target_stock');

            // otomatis dihitung backend:
            // target_stock - physical_stock
            // positif = kirim
            // negatif = tarik
            $table->integer('stock_difference');

            // harga konsinyasi saat itu (snapshot dari store_prices)
            $table->integer('price_snapshot');

            // fee snapshot (ambil dari products saat visit)
            $table->integer('fee_snapshot');

            $table->timestamps();

            $table->unique(['visit_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_items');
    }
};
