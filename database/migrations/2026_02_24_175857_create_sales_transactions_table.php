<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visit_id')
                  ->constrained('visits')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('store_id')
                  ->constrained('stores')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->date('transaction_date');

            $table->integer('total_amount')->default(0);
            // total rupiah dari seluruh item

            $table->integer('total_fee')->default(0);
            // total fee sales dari transaksi ini

            $table->enum('payment_type', [
                'consignment', // tambah piutang
                'cash'         // langsung lunas
            ]);

            $table->timestamps();

            $table->unique('visit_id');
            // 1 visit hanya boleh punya 1 transaksi
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_transactions');
    }
};
