<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->text('image_url')->nullable()->after('activity_level');
            $table->string('author')->nullable()->after('image_url');
            $table->text('source')->nullable()->after('author');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'author', 'source']);
        });
    }
};
