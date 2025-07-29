<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_profiles_id')->constrained('user_profiles')->onDelete('cascade');

            $table->date('date');
            $table->string('activity');
            $table->text('detail')->nullable();

            $table->integer('sleep')->nullable(); // dalam jam
            $table->foreignId('exercise_id')->nullable()->constrained('exercises')->onDelete('set null');
            $table->integer('duration')->nullable(); // durasi olahraga (menit)
            $table->bigInteger('steps')->nullable();
            $table->integer('water_intake')->nullable();   // ml
            $table->integer('calorie_intake')->nullable(); // kcal

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('activities');
    }
};
