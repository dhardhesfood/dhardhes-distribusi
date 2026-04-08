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
        Schema::create('product_pack_recipes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // index
            $table->index('product_id');

            // optional FK (boleh diaktifkan kalau mau strict)
            // $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_pack_recipes');
    }
};