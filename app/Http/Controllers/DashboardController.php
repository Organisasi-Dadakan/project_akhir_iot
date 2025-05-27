<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrafficLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Data terakhir per jalur (A, B, C)
        $latestLogs = TrafficLog::select('lane', 'vehicle_count', 'recorded_at')
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->groupBy('lane')
            ->map(function ($logs) {
                return $logs->first(); // ambil data terbaru per lane
            });

        // 2. Total kendaraan hari ini
        $todayTotal = TrafficLog::whereDate('recorded_at', Carbon::today())->sum('vehicle_count');

        // 3. Jalur terpadat hari ini
        $busiestLane = TrafficLog::selectRaw('lane, SUM(vehicle_count) as total')
            ->whereDate('recorded_at', Carbon::today())
            ->groupBy('lane')
            ->orderByDesc('total')
            ->first();

        // 4. Rata-rata kendaraan per jam hari ini
        $avgPerHour = TrafficLog::whereDate('recorded_at', Carbon::today())
            ->selectRaw('HOUR(recorded_at) as hour, SUM(vehicle_count) as total')
            ->groupBy('hour')
            ->get()
            ->avg('total');

        // 5. Semua log terbaru (limit 50)
        $allLogs = TrafficLog::orderBy('recorded_at', 'desc')
            ->limit(50)
            ->get();

        // 6. Data per jam untuk grafik kendaraan per jam
        $chartData = TrafficLog::whereDate('recorded_at', Carbon::today())
            ->selectRaw('HOUR(recorded_at) as hour, SUM(vehicle_count) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return view('dashboard.index', [
            'latestLogs' => $latestLogs,
            'todayTotal' => $todayTotal,
            'busiestLane' => $busiestLane,
            'avgPerHour' => round($avgPerHour, 2),
            'logs' => $allLogs,
            'chartData' => $chartData,
        ]);
    }
}
