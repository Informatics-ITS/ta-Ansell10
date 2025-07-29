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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_profiles_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['meal_reminder', 'intermittent_fasting', 'exercise_reminder', 'health_check', 'general']);
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->boolean('is_read')->default(false);
            $table->enum('status', ['pending', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index(['user_profiles_id', 'status', 'scheduled_at']);
            $table->index(['user_profiles_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};