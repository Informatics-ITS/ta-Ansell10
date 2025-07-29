<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('food_categories')->insert([
            ['name' => 'Nasi & Karbohidrat', 'icon' => 'KARBO'],
            ['name' => 'Protein', 'icon' => 'PROTEIN'],
            ['name' => 'Sayuran', 'icon' => 'SAYUR'],
            ['name' => 'Buah', 'icon' => 'BUAH'],
            ['name' => 'Minuman', 'icon' => 'MINUMAN'],
        ]);
    }
}
