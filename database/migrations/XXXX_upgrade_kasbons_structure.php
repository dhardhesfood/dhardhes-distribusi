<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kasbons', function (Blueprint $table) {

            // Rename amount → amount_total
            $table->renameColumn('amount', 'amount_total');

            // Tambah kolom baru
            $table->decimal('amount_paid', 15, 2)
                  ->default(0)
                  ->after('amount_total');

            $table->enum('type', ['manual','shortage','stock_conversion'])
                  ->default('manual')
                  ->after('amount_paid');

            $table->unsignedBigInteger('reference_id')
                  ->nullable()
                  ->after('type');

            $table->string('reference_type')
                  ->nullable()
                  ->after('reference_id');
        });
    }

    public function down(): void
    {
        Schema::table('kasbons', function (Blueprint $table) {

            $table->dropColumn([
                'amount_paid',
                'type',
                'reference_id',
                'reference_type'
            ]);

            $table->renameColumn('amount_total', 'amount');
        });
    }
};
