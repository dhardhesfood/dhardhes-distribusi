<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receivables', function (Blueprint $table) {

            // drop foreign key dulu
            $table->dropForeign(['sales_transaction_id']);

            // drop unique constraint
            $table->dropUnique(['sales_transaction_id']);

        });

        Schema::table('receivables', function (Blueprint $table) {

            // ubah jadi nullable
            $table->foreignId('sales_transaction_id')
                ->nullable()
                ->change();

            // pasang lagi foreign key
            $table->foreign('sales_transaction_id')
                ->references('id')
                ->on('sales_transactions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('receivables', function (Blueprint $table) {

            $table->dropForeign(['sales_transaction_id']);

        });

        Schema::table('receivables', function (Blueprint $table) {

            $table->foreignId('sales_transaction_id')
                ->nullable(false)
                ->change();

            $table->foreign('sales_transaction_id')
                ->references('id')
                ->on('sales_transactions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unique('sales_transaction_id');

        });
    }
};