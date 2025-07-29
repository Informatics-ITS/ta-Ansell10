<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FoodDiarySeeder extends Seeder
{
    public function run()
    {
        $profiles = [
            ['user_id' => 4, 'profile_id' => 4],
            ['user_id' => 4, 'profile_id' => 5],
        ];

        $start = Carbon::create(2025, 6, 1);
        $end = Carbon::create(2025, 6, 29);

        $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];
        $foodItemIds = [1, 4, 7, 13, 5, 2]; // contoh id food_items

        foreach ($profiles as $profile) {
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                foreach ($mealTypes as $meal) {
                    $diaryId = DB::table('food_diaries')->insertGetId([
                        'user_id' => $profile['user_id'],
                        'user_profiles_id' => $profile['profile_id'],
                        'date' => $date->toDateString(),
                        'meal_type' => $meal,
                        'notes' => "Makan $meal tanggal {$date->toDateString()}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Tambahkan 2-3 food_inputs per diary
                    $itemCount = rand(2, 3);
                    $selectedItemIds = collect($foodItemIds)->random($itemCount);

                    foreach ($selectedItemIds as $itemId) {
                        DB::table('food_inputs')->insert([
                            'food_diaries_id' => $diaryId,
                            'food_item_id' => $itemId,
                            'portion_size' => rand(1, 3),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $this->command->info("Seeder food_diaries selesai untuk semua profile milik user ID 4 dari {$start->toDateString()} s.d. {$end->toDateString()}");
    }
}
