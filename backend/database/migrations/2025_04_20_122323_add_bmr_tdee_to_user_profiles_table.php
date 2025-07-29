<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->decimal('bmr', 8, 2)->nullable()->after('activity_level');
            $table->decimal('tdee', 8, 2)->nullable()->after('bmr');
        });
    }

    public function down(): void {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['bmr', 'tdee']);
        });
    }

    /**
     * Reverse the migrations.
     */

};
