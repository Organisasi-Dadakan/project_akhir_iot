<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrafficLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $lanes = ['A', 'B', 'C'];
        for ($i = 0; $i < 100; $i++) {
            \App\Models\TrafficLog::create([
                'lane' => $lanes[array_rand($lanes)],
                'vehicle_count' => rand(1, 10),
                'recorded_at' => now()->subMinutes(rand(0, 1000)),
            ]);
        }
    }
}
