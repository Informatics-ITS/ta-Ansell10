<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FoodItem;

class FoodItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Existing data from previous seed
        FoodItem::create([
            'category_id' => 1,
            'name' => 'Nasi Putih',
            'image' => '/images/nasi-putih.jpg',
            'calories' => 130,
            'protein' => 2.7,
            'carbs' => 28,
            'fat' => 0.3,
        ]);

        FoodItem::create([
            'category_id' => 1,
            'name' => 'Nasi Merah',
            'image' => '/images/nasi-merah.jpg',
            'calories' => 110,
            'protein' => 2.6,
            'carbs' => 23,
            'fat' => 0.9,
        ]);

        FoodItem::create([
            'category_id' => 1,
            'name' => 'Roti Gandum',
            'image' => '/images/roti-gandum.jpg',
            'calories' => 69,
            'protein' => 2.5,
            'carbs' => 12,
            'fat' => 1.1,
        ]);

        FoodItem::create([
            'category_id' => 2,
            'name' => 'Ayam Goreng',
            'image' => '/images/ayam-goreng.jpg',
            'calories' => 250,
            'protein' => 20.5,
            'carbs' => 0,
            'fat' => 17.5,
        ]);

        FoodItem::create([
            'category_id' => 2,
            'name' => 'Telur Rebus',
            'image' => '/images/telur-rebus.jpg',
            'calories' => 78,
            'protein' => 6.3,
            'carbs' => 0.6,
            'fat' => 5.3,
        ]);

        FoodItem::create([
            'category_id' => 2,
            'name' => 'Ikan Salmon',
            'image' => '/images/ikan-salmon.jpg',
            'calories' => 208,
            'protein' => 20.4,
            'carbs' => 0,
            'fat' => 13.4,
        ]);

        FoodItem::create([
            'category_id' => 3,
            'name' => 'Bayam',
            'image' => '/images/bayam.jpg',
            'calories' => 23,
            'protein' => 2.9,
            'carbs' => 3.6,
            'fat' => 0.4,
        ]);

        FoodItem::create([
            'category_id' => 3,
            'name' => 'Brokoli',
            'image' => '/images/brokoli.jpg',
            'calories' => 34,
            'protein' => 2.8,
            'carbs' => 6.6,
            'fat' => 0.4,
        ]);

        FoodItem::create([
            'category_id' => 3,
            'name' => 'Wortel',
            'image' => '/images/wortel.jpg',
            'calories' => 41,
            'protein' => 0.9,
            'carbs' => 9.6,
            'fat' => 0.2,
        ]);

        FoodItem::create([
            'category_id' => 4,
            'name' => 'Pisang',
            'image' => '/images/pisang.jpg',
            'calories' => 89,
            'protein' => 1.1,
            'carbs' => 22.8,
            'fat' => 0.3,
        ]);

        FoodItem::create([
            'category_id' => 4,
            'name' => 'Apel',
            'image' => '/images/apel.jpg',
            'calories' => 52,
            'protein' => 0.3,
            'carbs' => 13.8,
            'fat' => 0.2,
        ]);

        FoodItem::create([
            'category_id' => 4,
            'name' => 'Jeruk',
            'image' => '/images/jeruk.jpg',
            'calories' => 47,
            'protein' => 0.9,
            'carbs' => 11.7,
            'fat' => 0.1,
        ]);

        FoodItem::create([
            'category_id' => 5,
            'name' => 'Air Putih',
            'image' => '/images/air-putih.jpg',
            'calories' => 0,
            'protein' => 0,
            'carbs' => 0,
            'fat' => 0,
        ]);

        FoodItem::create([
            'category_id' => 5,
            'name' => 'Susu',
            'image' => '/images/susu.jpg',
            'calories' => 102,
            'protein' => 8,
            'carbs' => 12,
            'fat' => 2.4,
        ]);

        FoodItem::create([
            'category_id' => 5,
            'name' => 'Jus Apel',
            'image' => '/images/jus-apel.jpg',
            'calories' => 46,
            'protein' => 0.2,
            'carbs' => 11.3,
            'fat' => 0.1,
        ]);

        // Additional new data entries based on your new list
        FoodItem::create([
            'category_id' => 1,
            'name' => 'Kentang Rebus',
            'image' => '/images/kentang-rebus.jpg',
            'calories' => 87,
            'protein' => 1.9,
            'carbs' => 20,
            'fat' => 0.1,
        ]);

        FoodItem::create([
            'category_id' => 1,
            'name' => 'Mie Instan',
            'image' => '/images/mie-instan.jpg',
            'calories' => 188,
            'protein' => 4.5,
            'carbs' => 30,
            'fat' => 7,
        ]);

        FoodItem::create([
            'category_id' => 2,
            'name' => 'Tempe Goreng',
            'image' => '/images/tempe-goreng.jpg',
            'calories' => 193,
            'protein' => 10.8,
            'carbs' => 14,
            'fat' => 12.5,
        ]);

        FoodItem::create([
            'category_id' => 2,
            'name' => 'Tahu Bacem',
            'image' => '/images/tahu-bacem.jpg',
            'calories' => 120,
            'protein' => 7.5,
            'carbs' => 3.5,
            'fat' => 8,
        ]);

        FoodItem::create([
            'category_id' => 3,
            'name' => 'Kacang Panjang',
            'image' => '/images/kacang-panjang.jpg',
            'calories' => 35,
            'protein' => 2.1,
            'carbs' => 7.2,
            'fat' => 0.3,
        ]);

        FoodItem::create([
            'category_id' => 3,
            'name' => 'Terong',
            'image' => '/images/terong.jpg',
            'calories' => 25,
            'protein' => 1,
            'carbs' => 5.7,
            'fat' => 0.2,
        ]);

        FoodItem::create([
            'category_id' => 4,
            'name' => 'Semangka',
            'image' => '/images/semangka.jpg',
            'calories' => 30,
            'protein' => 0.6,
            'carbs' => 7.5,
            'fat' => 0.2,
        ]);

        FoodItem::create([
            'category_id' => 5,
            'name' => 'Teh Manis',
            'image' => '/images/teh-manis.jpg',
            'calories' => 30,
            'protein' => 0,
            'carbs' => 8,
            'fat' => 0,
        ]);

        FoodItem::create([
            'category_id' => 5,
            'name' => 'Kopi Hitam',
            'image' => '/images/kopi-hitam.jpg',
            'calories' => 2,
            'protein' => 0.3,
            'carbs' => 0,
            'fat' => 0,
        ]);

        FoodItem::create([
            'category_id' => 5,
            'name' => 'Susu Sapi',
            'image' => '/images/susu-sapi.jpg',
            'calories' => 42,
            'protein' => 3.4,
            'carbs' => 5,
            'fat' => 1,
        ]);
    }
}
