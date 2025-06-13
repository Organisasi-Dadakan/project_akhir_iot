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
        $now = Carbon::now();

        foreach (range(0, 6) as $dayOffset) { // 7 hari terakhir
            foreach (range(0, 23) as $hour) { // per jam
                foreach ($jalurs as $jalur) {
                    Traffic::create([
                        'Jalur' => $jalur,
                        'jumlah_kendaraan' => rand(10, 100),
                        'durasi_lampu_hijau' => rand(20, 60),
                        'created_at' => $now->copy()->subDays($dayOffset)->setTime($hour, 0, 0),
                        'updated_at' => $now->copy()->subDays($dayOffset)->setTime($hour, 0, 0),
                    ]);
                }
            }
        }
    }
}
