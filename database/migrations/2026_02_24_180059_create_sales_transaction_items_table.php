<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_transaction_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sales_transaction_id')
                  ->constrained('sales_transactions')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->integer('quantity_sold');
            // hasil dari (system_stock - physical_stock)

            $table->integer('price_snapshot');
            // harga konsinyasi saat transaksi

            $table->integer('fee_snapshot');
            // fee produk saat transaksi

            $table->integer('subtotal_amount');
            // quantity_sold × price_snapshot

            $table->integer('subtotal_fee');
            // quantity_sold × fee_snapshot

            $table->timestamps();

            $table->unique(['sales_transaction_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_transaction_items');
    }
};
