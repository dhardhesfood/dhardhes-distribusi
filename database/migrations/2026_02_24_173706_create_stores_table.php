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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('area_id')
                  ->constrained('areas')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->string('name');
            $table->string('owner_name')->nullable();
            $table->string('phone')->nullable();

            $table->text('address')->nullable();
            $table->string('city')->nullable();

            $table->integer('visit_interval_days')->default(35);
            // interval default tetap 35, tapi fleksibel jika nanti mau override

            $table->date('last_visit_date')->nullable();
            // akan dipakai hitung jadwal kunjungan

            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
