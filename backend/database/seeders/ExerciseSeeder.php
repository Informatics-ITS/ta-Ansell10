<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $allExerciseData = [
            // Data dari gambar Anda (tanpa 'id')
            [
                'name' => 'Jogging (kecepatan sedang)',
                'category' => 'cardio',
                'met_value' => 7.5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bersepeda (santai)',
                'category' => 'cardio',
                'met_value' => 4.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // ... (lanjutkan semua data dari gambar Anda tanpa kolom 'id')
            [
                'name' => 'Lompat Tali',
                'category' => 'cardio',
                'met_value' => 12.3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Zumba',
                'category' => 'cardio',
                'met_value' => 6.5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Angkat Beban',
                'category' => 'strength',
                'met_value' => 6.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Push-Up',
                'category' => 'strength',
                'met_value' => 8.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pull-Up',
                'category' => 'strength',
                'met_value' => 8.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Yoga (ringan)',
                'category' => 'flexibility',
                'met_value' => 2.3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pilates',
                'category' => 'flexibility',
                'met_value' => 2.8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Menyapu Rumah',
                'category' => 'daily',
                'met_value' => 3.3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Naik Tangga',
                'category' => 'daily',
                'met_value' => 8.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Berkebun',
                'category' => 'daily',
                'met_value' => 4.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bermain Bola',
                'category' => 'other',
                'met_value' => 7.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Berenang (gaya bebas)',
                'category' => 'other',
                'met_value' => 9.8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Tambahan berdasarkan sumber ilmiah (tanpa 'id')
            [
                'name' => 'Jalan Cepat (brisk walking)',
                'category' => 'cardio',
                'met_value' => 4.3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lari (kecepatan umum)',
                'category' => 'cardio',
                'met_value' => 9.8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bersepeda (moderat)',
                'category' => 'cardio',
                'met_value' => 8.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Squat (bodyweight)',
                'category' => 'strength',
                'met_value' => 5.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lunges',
                'category' => 'strength',
                'met_value' => 5.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Plank',
                'category' => 'strength',
                'met_value' => 2.8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tai Chi',
                'category' => 'flexibility',
                'met_value' => 4.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mengepel Lantai',
                'category' => 'daily',
                'met_value' => 3.5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'HIIT (High-Intensity Interval Training)',
                'category' => 'cardio',
                'met_value' => 8.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bulu Tangkis (rekreasi)',
                'category' => 'other',
                'met_value' => 5.5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        // Aktivitas tambahan untuk lansia dan kognitif
            [
                'name' => 'Senam Lansia',
                'category' => 'flexibility',
                'met_value' => 2.8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Senam Diabetes',
                'category' => 'flexibility',
                'met_value' => 2.8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Senam Taichi',
                'category' => 'flexibility',
                'met_value' => 4.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bermain Catur',
                'category' => 'cognitive',
                'met_value' => 1.5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Membaca',
                'category' => 'cognitive',
                'met_value' => 1.3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Isi Teka-Teki Silang',
                'category' => 'cognitive',
                'met_value' => 1.3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bermain Puzzle',
                'category' => 'cognitive',
                'met_value' => 1.4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bermain Halma',
                'category' => 'cognitive',
                'met_value' => 1.4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Memasukkan semua data ke tabel exercises dalam satu query
        DB::table('exercises')->insert($allExerciseData);
    }
}