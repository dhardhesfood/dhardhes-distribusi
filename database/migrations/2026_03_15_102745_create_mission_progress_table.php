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
        Schema::create('mission_progress', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('mission_id');
            $table->unsignedBigInteger('user_id');

            $table->integer('progress')->default(0);

            $table->boolean('completed')->default(false);

            $table->timestamp('completed_at')->nullable();

            $table->boolean('reward_given')->default(false);

            $table->timestamps();

            $table->index(['mission_id','user_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_progress');
    }
};