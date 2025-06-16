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
        $jalurs = ['A', 'B', 'C'];
        $days = 7;
        $intervalMinutes = 2;

        for ($day = 0; $day < $days; $day++) {
            // Start from 00:00 of that day
            $startTime = Carbon::today()->subDays($day)->startOfDay();

            // Simulasikan 12 jam pertama dari hari itu (misalnya 06:00 - 18:00)
            $time = $startTime->copy()->addHours(6); // mulai dari jam 06:00
            $endTime = $startTime->copy()->addHours(18); // sampai jam 18:00

            while ($time < $endTime) {
                foreach ($jalurs as $jalur) {
                    Traffic::create([
                        'Jalur' => $jalur,
                        'jumlah_kendaraan' => rand(10, 100),
                        'durasi_lampu_hijau' => rand(30, 60),
                        'created_at' => $time->copy(),
                        'updated_at' => $time->copy(),
                    ]);
                }

                // Geser waktu ke 2 menit berikutnya
                $time->addMinutes($intervalMinutes);
            }
        }
    }
}
