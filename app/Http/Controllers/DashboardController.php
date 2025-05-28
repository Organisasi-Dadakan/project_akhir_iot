<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrafficLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Rekap per jalur hari ini
        $rekapPerLane = TrafficLog::whereDate('recorded_at', $today)
            ->selectRaw('lane, SUM(vehicle_count) as total')
            ->groupBy('lane')
            ->pluck('total', 'lane')
            ->toArray();

        // Isi nilai default jika jalur tidak lengkap
        $rekap = [
            'A' => $rekapPerLane['A'] ?? 0,
            'B' => $rekapPerLane['B'] ?? 0,
            'C' => $rekapPerLane['C'] ?? 0,
        ];
        $rekap['total'] = array_sum($rekap);

        // Jalur terpadat
        $rekap['terpadat'] = collect($rekap)
            ->only(['A', 'B', 'C'])
            ->sortDesc()
            ->keys()
            ->first();

        // Rata-rata per jam
        $avgPerHour = TrafficLog::whereDate('recorded_at', $today)
            ->selectRaw('HOUR(recorded_at) as hour, SUM(vehicle_count) as total')
            ->groupBy('hour')
            ->get()
            ->avg('total');
        $rekap['rata_rata'] = round($avgPerHour ?? 0, 2);

        // Grafik mingguan per jalur
        $grafik = TrafficLog::whereBetween('recorded_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->selectRaw("DAYNAME(recorded_at) as hari, lane as jalur, SUM(vehicle_count) as total")
            ->groupBy('hari', 'jalur')
            ->get();

        // Log hari ini
        $todayLogs = TrafficLog::whereDate('recorded_at', $today)
            ->orderByDesc('recorded_at')
            ->get()
            ->map(function ($log) {
                return (object)[
                    'waktu' => $log->recorded_at->format('H:i'),
                    'jalur' => $log->lane,
                    'jumlah_kendaraan' => $log->vehicle_count,
                ];
            });

        return view('dashboard', compact('rekap', 'grafik', 'todayLogs'));
    }
}
