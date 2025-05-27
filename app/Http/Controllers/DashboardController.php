<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrafficLog;

class DashboardController extends Controller
{
    public function index()
    {
        // $todayLogs = TrafficLog::whereDate('waktu', today())->get();

        // $rekap = [
        //     'A' => $todayLogs->where('jalur', 'A')->sum('jumlah_kendaraan'),
        //     'B' => $todayLogs->where('jalur', 'B')->sum('jumlah_kendaraan'),
        //     'C' => $todayLogs->where('jalur', 'C')->sum('jumlah_kendaraan'),
        //     'total' => $todayLogs->sum('jumlah_kendaraan'),
        //     'terpadat' => $todayLogs->groupBy('jalur')->map->sum('jumlah_kendaraan')->sortDesc()->keys()->first(),
        //     'rata_rata' => round($todayLogs->sum('jumlah_kendaraan') / 24, 1),
        // ];

        // $grafik = TrafficLog::selectRaw('DAYNAME(waktu) as hari, jalur, SUM(jumlah_kendaraan) as total')
        //     ->whereBetween('waktu', [now()->startOfWeek(), now()->endOfWeek()])
        //     ->groupBy('hari', 'jalur')
        //     ->get();
        $rekap = [
            'A' => 120,
            'B' => 80,
            'C' => 100,
            'total' => 300,
            'terpadat' => 'Jalur A',
            'rata_rata' => 42.8,
        ];

        $todayLogs = collect([
            (object) ['waktu' => '08:00', 'jalur' => 'A', 'jumlah_kendaraan' => 40],
            (object) ['waktu' => '09:00', 'jalur' => 'B', 'jumlah_kendaraan' => 25],
            (object) ['waktu' => '10:00', 'jalur' => 'C', 'jumlah_kendaraan' => 35],
        ]);

        $grafik = collect([
            (object) ['hari' => 'Senin', 'jalur' => 'A', 'total' => 100],
            (object) ['hari' => 'Selasa', 'jalur' => 'A', 'total' => 110],
            (object) ['hari' => 'Rabu', 'jalur' => 'A', 'total' => 130],
            (object) ['hari' => 'Kamis', 'jalur' => 'A', 'total' => 140],
            (object) ['hari' => 'Jumat', 'jalur' => 'A', 'total' => 120],
            (object) ['hari' => 'Sabtu', 'jalur' => 'A', 'total' => 90],
            (object) ['hari' => 'Minggu', 'jalur' => 'A', 'total' => 70],

            (object) ['hari' => 'Senin', 'jalur' => 'B', 'total' => 80],
            (object) ['hari' => 'Selasa', 'jalur' => 'B', 'total' => 60],
            (object) ['hari' => 'Rabu', 'jalur' => 'B', 'total' => 75],
            (object) ['hari' => 'Kamis', 'jalur' => 'B', 'total' => 90],
            (object) ['hari' => 'Jumat', 'jalur' => 'B', 'total' => 100],
            (object) ['hari' => 'Sabtu', 'jalur' => 'B', 'total' => 85],
            (object) ['hari' => 'Minggu', 'jalur' => 'B', 'total' => 70],
        ]);

        return view('dashboard', compact('rekap', 'todayLogs', 'grafik'));
    }
}
