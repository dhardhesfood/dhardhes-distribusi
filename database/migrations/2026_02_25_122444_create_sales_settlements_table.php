<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_settlements', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('created_by');
            $table->date('settlement_date');

            $table->decimal('total_sales_amount', 15, 2)->default(0);
            $table->decimal('total_receivable_payment', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);

            $table->decimal('expected_amount', 15, 2)->default(0);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->decimal('shortage_amount', 15, 2)->default(0);

            $table->enum('status', ['draft','closed'])
                  ->default('closed');

            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->unique(['user_id','settlement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_settlements');
    }
};
