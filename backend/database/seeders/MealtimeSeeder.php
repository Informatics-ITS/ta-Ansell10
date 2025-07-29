<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MealtimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menambahkan data waktu makan ke tabel mealtime
        DB::table('mealtime')->insert([
            [
                'waktu_makan' => 'sarapan',
                'waktu_mulai' => '06:00:00',
                'waktu_selesai' => '09:00:00',
            ],
            [
                'waktu_makan' => 'makan_siang',
                'waktu_mulai' => '12:00:00',
                'waktu_selesai' => '14:00:00',
            ],
            [
                'waktu_makan' => 'makan_malam',
                'waktu_mulai' => '17:00:00',
                'waktu_selesai' => '19:00:00',
            ]
        ]);
    }
}
