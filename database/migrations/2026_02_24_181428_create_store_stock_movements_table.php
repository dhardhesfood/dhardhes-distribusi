<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                  ->constrained('stores')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->integer('quantity');
            // + = tambah stok toko
            // - = kurangi stok toko

            $table->enum('type', [
                'send_from_sales',
                'return_to_sales',
                'sale',
                'adjustment'
            ]);

            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_stock_movements');
    }
};
