<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads_reports', function (Blueprint $table) {
            $table->id();

            // TANGGAL IKLAN
            $table->date('date');

            // DATA DARI FB ADS
            $table->decimal('budget', 12, 2)->default(0); // biaya
            $table->integer('impressions')->default(0); // tayangan ke landing
            $table->integer('link_clicks')->default(0); // klik link
            $table->integer('results')->default(0); // klik WA (hasil FB)
            $table->decimal('cost_per_result', 12, 2)->default(0); // biaya per hasil

            // INPUT MANUAL (MENYUSUL)
            $table->integer('real_chat_wa')->default(0); // chat WA real
            $table->integer('closing')->default(0); // closing real

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads_reports');
    }
};