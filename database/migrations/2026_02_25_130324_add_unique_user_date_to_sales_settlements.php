<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_settlements', function (Blueprint $table) {
            $table->unique(['user_id','settlement_date'], 'unique_user_date_settlement');
        });
    }

    public function down(): void
    {
        Schema::table('sales_settlements', function (Blueprint $table) {
            $table->dropUnique('unique_user_date_settlement');
        });
    }
};
