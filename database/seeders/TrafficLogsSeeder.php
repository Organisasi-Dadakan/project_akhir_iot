<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrafficLogsSeeder extends Seeder
{
    public function run()
    {
        $jalur = ['A', 'B', 'C'];
        $now = Carbon::now();

        // Insert 20 data dummy dalam rentang waktu hari ini
        for ($i = 0; $i < 20; $i++) {
            DB::table('traffic_logs')->insert([
                'waktu' => $now->copy()->subHours(rand(0, 12))->format('Y-m-d H:i:s'),
                'jalur' => $jalur[array_rand($jalur)],
                'jumlah_kendaraan' => rand(10, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
