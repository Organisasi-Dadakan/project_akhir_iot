<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Console\Commands\sendToBlynk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class BlynkController extends Controller
{
    public function kirim()
    {
        $token = env('BLYNK_TOKEN');

        // Daftar jalur yang tersedia
        $jalurList = ['A', 'B', 'C'];

        // Mapping pin Blynk
        $pinMap = [
            'A' => ['jumlah_kendaraan_rt' => 'V0', 'durasi_lampu_hijau' => 'V5'],
            'B' => ['jumlah_kendaraan_rt' => 'V1', 'durasi_lampu_hijau' => 'V6'],
            'C' => ['jumlah_kendaraan_rt' => 'V2', 'durasi_lampu_hijau' => 'V7'],
        ];

        $pinMerahMap = [
            'A' => 'V8',
            'B' => 'V9',
            'C' => 'V10',
        ];

        $pinChartMap = [
            'A' => 'V11',
            'B' => 'V12',
            'C' => 'V13',
        ];

        $hasil = [];
        $jumlahPerJalur = [];
        $status_terpadat = null;
        $totalSiklusMerah = 120;

        // 🔵 Kirim jumlah kendaraan per jam ke V3
        $now = Carbon::now('Asia/Jakarta');
        $start = $now->copy()->startOfHour();
        $end = $now->copy()->endOfHour();

        $jumlahKendaraanPerJam = DB::table('traffics')
            ->whereBetween('created_at', [$start, $end])
            ->sum('jumlah_kendaraan');

        $resV3 = Http::get("http://blynk.cloud/external/api/update", [
            'token' => $token,
            'V3' => $jumlahKendaraanPerJam,
        ]);

        $hasil[] = [
            'jalur' => 'Semua Jalur',
            'jam' => $start->format('H:i') . ' - ' . $end->format('H:i'),
            'jumlah_kendaraan_per_jam' => $jumlahKendaraanPerJam,
            'status' => $resV3->successful(),
        ];

        // 🔁 Loop tiap jalur
        foreach ($jalurList as $jalur) {
            $data = DB::table('traffics')
                ->where('Jalur', $jalur)
                ->orderByDesc('created_at')
                ->first();

            if ($data) {
                $jumlah = $data->jumlah_kendaraan;
                $durasi = $data->durasi_lampu_hijau;
                $durasiMerah = $totalSiklusMerah - $durasi;

                $jumlahPerJalur[$jalur] = $jumlah;

                // Kirim jumlah kendaraan realtime
                $res1 = Http::get("http://blynk.cloud/external/api/update", [
                    'token' => $token,
                    $pinMap[$jalur]['jumlah_kendaraan_rt'] => $jumlah,
                ]);

                // Kirim durasi hijau
                $res2 = Http::get("http://blynk.cloud/external/api/update", [
                    'token' => $token,
                    $pinMap[$jalur]['durasi_lampu_hijau'] => $durasi,
                ]);

                // Kirim durasi merah
                $res3 = Http::get("http://blynk.cloud/external/api/update", [
                    'token' => $token,
                    $pinMerahMap[$jalur] => $durasiMerah,
                ]);

                // Kirim juga ke Chart
                $resChart = Http::get("http://blynk.cloud/external/api/update", [
                    'token' => $token,
                    'pin' => $pinChartMap[$jalur],
                    'value' => $jumlah,
                ]);

                $hasil[] = [
                    'jalur' => $jalur,
                    'jumlah_kendaraan' => $res1->successful(),
                    'durasi_lampu_hijau' => $res2->successful(),
                    'durasi_lampu_merah' => $res3->successful(),
                    'jumlah_chart' => $resChart->successful(),
                ];
            }
        }

        // 🟥 Kirim jalur terpadat ke V4
        if (!empty($jumlahPerJalur)) {
            $jalurTerpadat = array_keys($jumlahPerJalur, max($jumlahPerJalur))[0];
            $labelTerpadat = "Jalur " . $jalurTerpadat;

            $res4 = Http::get("http://blynk.cloud/external/api/update", [
                'token' => $token,
                'V4' => $labelTerpadat,
            ]);

            $status_terpadat = $res4->successful();
        }

        return view('blynk.status', compact('hasil', 'status_terpadat'));
    }

    public function handle()
    {
        Artisan::call('blynk:send');
        return redirect()->back()->with('success', 'Data berhasil dikirim ke Blynk.');
    }
}
