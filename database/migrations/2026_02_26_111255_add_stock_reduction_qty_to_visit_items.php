<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visit_items', function (Blueprint $table) {
            $table->integer('stock_reduction_qty')
                  ->default(0)
                  ->after('bonus_qty');
        });
    }

    public function down(): void
    {
        Schema::table('visit_items', function (Blueprint $table) {
            $table->dropColumn('stock_reduction_qty');
        });
    }
};