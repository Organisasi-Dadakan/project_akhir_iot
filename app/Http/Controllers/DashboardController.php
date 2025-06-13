<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Traffic;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Rekap kendaraan per jalur hari ini
        $rekapPerLane = Traffic::whereDate('created_at', $today)
            ->selectRaw('Jalur, SUM(jumlah_kendaraan) as total')
            ->groupBy('Jalur')
            ->pluck('total', 'Jalur')
            ->toArray();

        $rekap = [
            'A' => $rekapPerLane['A'] ?? 0,
            'B' => $rekapPerLane['B'] ?? 0,
            'C' => $rekapPerLane['C'] ?? 0,
        ];
        $rekap['total'] = array_sum($rekap);
        $rekap['terpadat'] = collect($rekap)
            ->only(['A', 'B', 'C'])
            ->sortDesc()
            ->keys()
            ->first();

        // Rata-rata per jam
        $avgPerHour = Traffic::whereDate('created_at', $today)
            ->selectRaw('HOUR(created_at) as hour, SUM(jumlah_kendaraan) as total')
            ->groupBy('hour')
            ->get()
            ->avg('total');
        $rekap['rata_rata'] = round($avgPerHour ?? 0, 2);

        // Grafik mingguan per jalur
        $grafik = Traffic::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->selectRaw("DAYNAME(created_at) as hari, Jalur, SUM(jumlah_kendaraan) as total")
            ->groupBy('hari', 'Jalur')
            ->get();

        // Log hari ini
        $todayLogs = Traffic::whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($log) {
                return (object)[
                    'waktu' => $log->created_at->format('H:i'),
                    'jalur' => $log->Jalur,
                    'jumlah_kendaraan' => $log->jumlah_kendaraan,
                ];
            });

        return view('dashboard', compact('rekap', 'grafik', 'todayLogs'));
    }
}
