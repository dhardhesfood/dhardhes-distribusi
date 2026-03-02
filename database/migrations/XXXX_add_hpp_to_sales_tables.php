<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_transaction_items', function (Blueprint $table) {
            $table->decimal('subtotal_hpp', 15, 2)->default(0)->after('subtotal_fee');
        });

        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->decimal('total_hpp', 15, 2)->default(0)->after('total_fee');
        });
    }

    public function down(): void
    {
        Schema::table('sales_transaction_items', function (Blueprint $table) {
            $table->dropColumn('subtotal_hpp');
        });

        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropColumn('total_hpp');
        });
    }
};
