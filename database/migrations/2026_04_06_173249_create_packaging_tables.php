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
        Schema::create('packaging_stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->cascadeOnDelete();

            $table->integer('stock_qty')->default(0);

            $table->timestamps();

            $table->unique(['product_id','product_variant_id']);
        });

        Schema::create('packaging_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->cascadeOnDelete();

            $table->string('type'); // in, out, return
            $table->integer('quantity');

            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->index(['product_id']);
            $table->index(['product_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packaging_movements');
        Schema::dropIfExists('packaging_stocks');
    }
};