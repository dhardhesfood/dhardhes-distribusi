<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kasbons', function (Blueprint $table) {

            $table->foreignId('user_id')
                  ->after('id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('created_by')
                  ->after('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->decimal('amount', 15, 2)
                  ->after('created_by');

            $table->text('description')
                  ->nullable()
                  ->after('amount');

            $table->enum('status', ['open','settled'])
                  ->default('open')
                  ->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('kasbons', function (Blueprint $table) {

            $table->dropForeign(['user_id']);
            $table->dropForeign(['created_by']);

            $table->dropColumn([
                'user_id',
                'created_by',
                'amount',
                'description',
                'status'
            ]);
        });
    }
};
