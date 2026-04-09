<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('warehouse_variant_stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();

            $table->integer('stock_qty')->default(0);

            $table->timestamps();

            $table->unique(['product_id','product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_variant_stocks');
    }
};
