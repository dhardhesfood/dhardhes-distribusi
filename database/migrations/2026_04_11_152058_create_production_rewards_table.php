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
    Schema::create('production_rewards', function (Blueprint $table) {
        $table->id();

        // periode
        $table->unsignedTinyInteger('month');
        $table->unsignedSmallInteger('year');

        // data produksi
        $table->bigInteger('total_gram')->default(0);

        // nominal reward
        $table->bigInteger('reward_amount')->default(0);

        // status lock
        $table->boolean('is_locked')->default(false);
        $table->timestamp('locked_at')->nullable();

        // status pembayaran
        $table->boolean('is_paid')->default(false);
        $table->timestamp('paid_at')->nullable();

        $table->timestamps();

        // 🔥 biar tidak double data per bulan
        $table->unique(['month', 'year']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_rewards');
    }
};
