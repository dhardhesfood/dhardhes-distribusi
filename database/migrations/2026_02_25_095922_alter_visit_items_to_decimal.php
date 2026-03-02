<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE visit_items
            MODIFY price_snapshot DECIMAL(15,2) NOT NULL,
            MODIFY fee_snapshot DECIMAL(15,2) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE visit_items
            MODIFY price_snapshot INT NOT NULL,
            MODIFY fee_snapshot INT NOT NULL
        ");
    }
};
