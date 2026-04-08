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
        Schema::create('product_pack_recipe_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('recipe_id');
            $table->unsignedBigInteger('product_variant_id');

            $table->integer('qty_per_pack'); // contoh: 1 pack = 3 pcs

            $table->timestamps();

            // index
            $table->index('recipe_id');
            $table->index('product_variant_id');

            // optional FK
            // $table->foreign('recipe_id')->references('id')->on('product_pack_recipes')->cascadeOnDelete();
            // $table->foreign('product_variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_pack_recipe_items');
    }
};