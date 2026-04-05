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
    Schema::table('worker_withdrawals', function (Blueprint $table) {
    $table->decimal('requested_amount', 15, 2);
    $table->decimal('approved_amount', 15, 2)->nullable();

    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

    $table->timestamp('approved_at')->nullable();
    $table->unsignedBigInteger('approved_by')->nullable();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
