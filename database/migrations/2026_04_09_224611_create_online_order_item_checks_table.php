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
       Schema::create('online_order_item_checks', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('online_order_id');
    $table->unsignedBigInteger('product_id');
    $table->unsignedBigInteger('product_variant_id');

    $table->integer('required_qty');
    $table->integer('available_qty');

    $table->string('status'); // cukup / kurang
    $table->integer('shortage_qty')->default(0);

    $table->timestamps();

    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_order_item_checks');
    }
};
