<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            // Ubah kolom 'activity' menjadi nullable
            $table->string('activity')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            // Jika rollback, kembalikan menjadi NOT NULL (tanpa default)
            $table->string('activity')->nullable(false)->change();
        });
    }
};
