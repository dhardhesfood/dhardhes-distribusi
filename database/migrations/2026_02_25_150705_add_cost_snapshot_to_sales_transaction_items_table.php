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
        Schema::table('sales_transaction_items', function (Blueprint $table) {

            // Tambah cost snapshot
            $table->decimal('cost_snapshot', 15, 2)
                  ->default(0)
                  ->after('fee_snapshot');

            // Perbaiki tipe data agar akurat (uang harus decimal)
            $table->decimal('price_snapshot', 15, 2)->change();
            $table->decimal('fee_snapshot', 15, 2)->change();
            $table->decimal('subtotal_amount', 15, 2)->change();
            $table->decimal('subtotal_fee', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_transaction_items', function (Blueprint $table) {

            $table->dropColumn('cost_snapshot');

            // Kembalikan ke int jika rollback
            $table->integer('price_snapshot')->change();
            $table->integer('fee_snapshot')->change();
            $table->integer('subtotal_amount')->change();
            $table->integer('subtotal_fee')->change();
        });
    }
};
