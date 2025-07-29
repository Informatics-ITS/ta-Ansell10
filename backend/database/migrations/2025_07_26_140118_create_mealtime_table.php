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
        Schema::create('mealtime', function (Blueprint $table) {
            $table->id();

            // Jenis waktu makan (sarapan, makan siang, makan malam)
            $table->enum('waktu_makan', ['sarapan', 'makan_siang', 'makan_malam']);

            // Waktu mulai
            $table->time('waktu_mulai');

            // Waktu selesai
            $table->time('waktu_selesai');

            // Timestamps (created_at, updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mealtime');
    }
};
