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
        Schema::create('missions', function (Blueprint $table) {
            $table->id();

            $table->string('title'); // Judul misi

            $table->string('type'); 
            // contoh:
            // visit_count
            // product_sales
            // new_store
            // revenue

            $table->unsignedBigInteger('product_id')->nullable(); 
            // digunakan jika misi terkait produk

            $table->integer('target'); 
            // target angka misi

            $table->decimal('reward_amount', 12, 2)->default(0); 
            // reward uang

            $table->date('start_date'); 
            $table->date('end_date');

            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};