<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['min_tdee', 'max_tdee']);
            $table->enum('activity_level', ['sedentary', 'light', 'moderate', 'active', 'very active'])->after('tag');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('activity_level');
            $table->unsignedInteger('min_tdee')->nullable();
            $table->unsignedInteger('max_tdee')->nullable();
        });
    }
};

