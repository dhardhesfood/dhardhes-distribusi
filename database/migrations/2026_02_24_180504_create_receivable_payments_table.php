<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('receivable_id')
                  ->constrained('receivables')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            // siapa yang menerima pembayaran (sales/admin)

            $table->integer('amount');
            // nominal pembayaran

            $table->enum('payment_method', [
                'cash',
                'transfer'
            ]);

            $table->date('payment_date');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivable_payments');
    }
};
