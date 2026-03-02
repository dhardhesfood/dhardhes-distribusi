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
        Schema::create('cash_sales', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->date('sale_date');

            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('discount')->default(0); // nominal discount
            $table->bigInteger('total')->default(0);

            $table->enum('payment_method', ['cash', 'transfer']);
            $table->bigInteger('paid_amount')->default(0);
            $table->bigInteger('kasbon_amount')->default(0);

            $table->bigInteger('fee_total')->default(0);

            $table->enum('status', ['draft', 'locked'])->default('draft');

            $table->timestamps();

            // Foreign Key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Index
            $table->index(['user_id', 'sale_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_sales');
    }
};