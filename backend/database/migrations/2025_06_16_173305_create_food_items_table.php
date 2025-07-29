<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoodItemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('food_items', function (Blueprint $table) {
            $table->id(); // id dengan auto_increment
            $table->foreignId('category_id')->constrained('food_categories')->onDelete('cascade'); // Foreign key untuk category_id
            $table->string('name', 255); // Kolom name dengan tipe varchar(255)
            $table->string('image', 255)->nullable(); // Kolom image dengan tipe varchar(255), nullable
            $table->double('calories')->default(0); // Kolom calories dengan tipe double dan default 0
            $table->double('protein')->default(0); // Kolom protein dengan tipe double dan default 0
            $table->double('carbs')->default(0); // Kolom carbs dengan tipe double dan default 0
            $table->double('fat')->default(0); // Kolom fat dengan tipe double dan default 0
            $table->timestamps(); // Kolom created_at dan updated_at bertipe timestamp, nullable secara default
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_items');
    }
}
