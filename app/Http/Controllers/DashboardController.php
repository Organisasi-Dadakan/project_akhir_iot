<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $jalurTables = [
            'A' => 'jalur_a',
            'B' => 'jalur_b',
            'C' => 'jalur_c',
        ];

        // Rekap jumlah kendaraan per jalur hari ini
        $rekap = [];
        foreach ($jalurTables as $key => $table) {
            $rekap[$key] = DB::table($table)
                ->whereDate('timestamp', $today)
                ->sum('jumlah_kendaraan');
        }
        $rekap['total'] = array_sum($rekap);
        $rekap['terpadat'] = collect($rekap)
            ->only(['A', 'B', 'C'])
            ->sortDesc()
            ->keys()
            ->first();

        // Rata-rata kendaraan per jam hari ini (gabungan)
        $allTodayLogs = collect();
        foreach ($jalurTables as $jalur => $table) {
            $logs = DB::table($table)
                ->whereDate('timestamp', $today)
                ->selectRaw("HOUR(timestamp) as hour, jumlah_kendaraan")
                ->get()
                ->map(function ($log) use ($jalur) {
                    return [
                        'hour' => $log->hour,
                        'jumlah_kendaraan' => $log->jumlah_kendaraan,
                        'jalur' => $jalur,
                    ];
                });
            $allTodayLogs = $allTodayLogs->merge($logs);
        }
        $rekap['rata_rata'] = round(
            $allTodayLogs->groupBy('hour')->map->sum('jumlah_kendaraan')->avg() ?? 0,
            2
        );

        // Grafik mingguan per jalur
        $grafik = [];
        foreach ($jalurTables as $jalur => $table) {
            $grafik[$jalur] = DB::table($table)
                ->whereBetween('timestamp', [now()->startOfWeek(), now()->endOfWeek()])
                ->selectRaw("DAYNAME(timestamp) as hari, SUM(jumlah_kendaraan) as total")
                ->groupBy('hari')
                ->pluck('total', 'hari')
                ->toArray();
        }

        // Log hari ini (gabungan)
        $todayLogs = collect();
        foreach ($jalurTables as $jalur => $table) {
            $logs = DB::table($table)
                ->whereDate('timestamp', $today)
                ->orderByDesc('timestamp')
                ->get()
                ->map(function ($log) use ($jalur) {
                    return (object)[
                        'waktu' => Carbon::parse($log->timestamp)->format('H:i'),
                        'jalur' => $jalur,
                        'jumlah_kendaraan' => $log->jumlah_kendaraan,
                    ];
                });
            $todayLogs = $todayLogs->merge($logs);
        }

        $todayLogs = $todayLogs->sortByDesc('waktu');

        return view('dashboard', compact('rekap', 'grafik', 'todayLogs'));
    }
}
