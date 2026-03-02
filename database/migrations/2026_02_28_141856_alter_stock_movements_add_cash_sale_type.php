<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE stock_movements 
            MODIFY type ENUM(
                'warehouse_out',
                'warehouse_in',
                'send_to_store',
                'return_from_store',
                'adjustment',
                'conversion_sale',
                'bonus',
                'cash_sale'
            ) NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE stock_movements 
            MODIFY type ENUM(
                'warehouse_out',
                'warehouse_in',
                'send_to_store',
                'return_from_store',
                'adjustment',
                'conversion_sale',
                'bonus'
            ) NOT NULL
        ");
    }
};