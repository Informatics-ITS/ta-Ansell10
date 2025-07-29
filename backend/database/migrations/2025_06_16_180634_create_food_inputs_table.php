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
        Schema::create('food_inputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_diaries_id')->constrained('food_diaries')->onDelete('cascade');  // Hubungkan dengan food_diaries
            $table->foreignId('food_item_id')->constrained('food_items')->onDelete('cascade');   // Hubungkan dengan food_items
            $table->decimal('portion_size', 5, 2);  // Ukuran porsi untuk food item
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_inputs');
    }
};
