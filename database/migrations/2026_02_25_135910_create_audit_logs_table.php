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
        Schema::create('audit_logs', function (Blueprint $table) {

            $table->id();

            // Siapa yang melakukan aksi
            $table->unsignedBigInteger('user_id');

            // Model yang diaudit (contoh: App\Models\SalesSettlement)
            $table->string('auditable_type');

            // ID record yang diaudit
            $table->unsignedBigInteger('auditable_id');

            // created / updated / deleted
            $table->string('action');

            // Data sebelum perubahan (nullable)
            $table->json('old_values')->nullable();

            // Data sesudah perubahan (nullable)
            $table->json('new_values')->nullable();

            $table->timestamps();

            // Index untuk performa query
            $table->index(['auditable_type','auditable_id']);
            $table->index('user_id');

            // Foreign key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
