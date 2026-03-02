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
        Schema::create('sales_fee_payments', function (Blueprint $table) {
            $table->id();

            // Sales yang menerima fee
            $table->unsignedBigInteger('user_id');

            // Nominal yang dibayarkan
            $table->bigInteger('amount_paid');

            // Tanggal pembayaran
            $table->date('payment_date');

            // Catatan opsional
            $table->text('notes')->nullable();

            // Admin yang melakukan pembayaran
            $table->unsignedBigInteger('created_by');

            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Index untuk performa query
            $table->index('user_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_fee_payments');
    }
};