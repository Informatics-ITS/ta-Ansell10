<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivitySeeder extends Seeder
{
    public function run()
    {
        // Sama user_id, beda user_profiles_id
        $profiles = [
            ['user_id' => 4, 'profile_id' => 4],
            ['user_id' => 4, 'profile_id' => 5],
        ];

        $start = Carbon::create(2025, 6, 1);
        $end   = Carbon::create(2025, 6, 29);

        $exerciseNames = ['Jogging Pagi', 'Senam Ringan', 'Yoga', 'Lari Sore', 'Push-up'];
        $exerciseIds   = [1, 2, 3, 4, 5];

        foreach ($profiles as $profile) {
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $randomIndex = rand(0, count($exerciseNames) - 1);

                DB::table('activities')->insert([
                    'user_id'           => $profile['user_id'],
                    'user_profiles_id'  => $profile['profile_id'],
                    'date'              => $date->toDateString(),
                    'activity'          => $exerciseNames[$randomIndex],
                    'detail'            => $exerciseNames[$randomIndex] . ' selama ' . rand(30, 90) . ' menit',
                    'sleep'             => rand(6, 9),
                    'exercise_id'       => $exerciseIds[$randomIndex],
                    'duration'          => rand(30, 90),
                    'steps'             => rand(2000, 7000),
                    'water_intake'      => rand(1000, 2000),
                    'calorie_intake'    => rand(1600, 2500),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }

        $this->command->info("Seeder aktivitas selesai untuk semua profile milik user ID 4 dari {$start->toDateString()} s.d. {$end->toDateString()}");
    }
}
