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
        Schema::create('sales_stock_sessions', function (Blueprint $table) {
            $table->id();

            // Sales (user)
            $table->unsignedBigInteger('user_id');

            // Admin / gudang yang membuat session
            $table->unsignedBigInteger('created_by')->nullable();

            // Periode session
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();

            // Status session
            $table->enum('status', ['open','done','minus'])
                  ->default('open');

            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_stock_sessions');
    }
};