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
    Schema::table('exercises', function (Blueprint $table) {
        $table->dropColumn('description');
    });
}

public function down(): void
{
    Schema::table('exercises', function (Blueprint $table) {
        $table->text('description')->nullable();
    });
}

};
