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
        // 1. Drop unique index on name
        DB::statement('ALTER TABLE products DROP INDEX products_name_unique');

        // 2. Rename sales_fee to default_fee_nominal and change type to DECIMAL(15,2)
        DB::statement('ALTER TABLE products 
            CHANGE sales_fee default_fee_nominal DECIMAL(15,2) NOT NULL');

        // 3. Change base_price to DECIMAL(15,2)
        DB::statement('ALTER TABLE products 
            MODIFY base_price DECIMAL(15,2) NULL');

        // 4. Add unit column
        DB::statement("ALTER TABLE products 
            ADD COLUMN unit VARCHAR(50) NOT NULL DEFAULT 'Pcs' 
            AFTER default_fee_nominal");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse 4. Drop unit column
        DB::statement('ALTER TABLE products DROP COLUMN unit');

        // Reverse 3. Change base_price back to INT
        DB::statement('ALTER TABLE products 
            MODIFY base_price INT NULL');

        // Reverse 2. Rename default_fee_nominal back to sales_fee and change type to INT
        DB::statement('ALTER TABLE products 
            CHANGE default_fee_nominal sales_fee INT NOT NULL');

        // Reverse 1. Add unique index back to name
        DB::statement('ALTER TABLE products 
            ADD UNIQUE products_name_unique (name)');
    }
};
