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
        Schema::create('sales_total_rewards', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->integer('month');
            $table->integer('year');

            $table->decimal('kpi_reward', 15, 2)->default(0);
            $table->decimal('mission_reward', 15, 2)->default(0);

            $table->decimal('total_reward', 15, 2)->default(0);

            $table->timestamp('calculated_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_total_rewards');
    }
};