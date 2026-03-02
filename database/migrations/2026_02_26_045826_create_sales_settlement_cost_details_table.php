<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_settlement_cost_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sales_settlement_id')
                ->constrained('sales_settlements')
                ->onDelete('cascade');

            $table->enum('jenis_biaya', [
                'bensin',
                'parkir',
                'makan',
                'tol',
                'lain_lain'
            ]);

            $table->decimal('nominal', 15, 2);

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_settlement_cost_details');
    }
};
