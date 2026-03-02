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
        Schema::create('product_cost_histories', function (Blueprint $table) {

            $table->id();

            // Relasi ke produk
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();

            // Cost berlaku
            $table->decimal('cost', 15, 2);

            // Tanggal mulai berlaku
            $table->date('effective_date');

            // Siapa yang set cost
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();

            // Index penting untuk query cepat
            $table->index(['product_id', 'effective_date']);

            // Prevent duplicate effective date per product
            $table->unique(['product_id', 'effective_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_cost_histories');
    }
};
