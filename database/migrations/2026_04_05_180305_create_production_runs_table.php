<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_runs', function (Blueprint $table) {
            $table->id();

            // relasi ke produk (mie mentah)
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // 🔥 hasil produksi (GRAM)
            $table->bigInteger('output_gram');

            // 🔥 snapshot biaya bahan
            $table->decimal('total_material_cost', 15, 2);

            // 🔥 ongkos produksi
            $table->decimal('labor_rate_per_gram', 10, 4)->default(1.8); // default 1.8 (1800/kg)
            $table->decimal('labor_percentage', 5, 2); // contoh: 1, 0.5, 1.3
            $table->decimal('total_labor_cost', 15, 2);

            // 🔥 hasil akhir
            $table->decimal('hpp_per_gram', 15, 6);

            // optional
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_runs');
    }
};