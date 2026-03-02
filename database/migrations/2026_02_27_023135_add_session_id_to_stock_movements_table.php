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
        Schema::table('stock_movements', function (Blueprint $table) {

            $table->unsignedBigInteger('session_id')
                ->nullable()
                ->after('reference_type');

            $table->foreign('session_id')
                ->references('id')
                ->on('sales_stock_sessions')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {

            $table->dropForeign(['session_id']);
            $table->dropColumn('session_id');
        });
    }
};