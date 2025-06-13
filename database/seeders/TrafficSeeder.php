<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Traffic;
use Carbon\Carbon;

class TrafficSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $start = Carbon::now()->subHour(); // mulai dari 1 jam yang lalu
        $data = [];

        for ($i = 0; $i < 30; $i++) { // 30 cycle (2 menit sekali = 1 jam)
            $timestamp = $start->copy()->addMinutes($i * 2);

            foreach (['A', 'B', 'C'] as $jalur) {
                $data[] = [
                    'Jalur' => $jalur,
                    'jumlah_kendaraan' => rand(10, 100),
                    'durasi_lampu_hijau' => rand(20, 60),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        Traffic::insert($data);
    }
}
