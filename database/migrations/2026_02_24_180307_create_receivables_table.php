<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sales_transaction_id')
                  ->constrained('sales_transactions')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('store_id')
                  ->constrained('stores')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->integer('total_amount');
            // total piutang dari transaksi

            $table->integer('paid_amount')->default(0);
            // total yang sudah dibayar

            $table->integer('remaining_amount');
            // sisa piutang (total_amount - paid_amount)

            $table->enum('status', [
                'unpaid',
                'partial',
                'paid'
            ])->default('unpaid');

            $table->date('due_date')->nullable();
            // opsional kalau mau pakai jatuh tempo

            $table->timestamps();

            $table->unique('sales_transaction_id');
            // 1 transaksi hanya punya 1 piutang
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivables');
    }
};
