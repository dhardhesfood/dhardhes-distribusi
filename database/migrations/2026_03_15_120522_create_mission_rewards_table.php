<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mission_rewards', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('mission_id');
            $table->unsignedBigInteger('user_id');

            $table->decimal('reward_amount', 15, 2)->default(0);

            $table->timestamp('reward_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_rewards');
    }
};