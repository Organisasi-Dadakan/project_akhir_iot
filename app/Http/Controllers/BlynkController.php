<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Console\Commands\sendToBlynk;
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
        
        $hasil = [];
        $jumlahPerJalur = [];
        $status_terpadat = null;
        $totalSiklusMerah = 120;

        foreach ($jalurList as $jalur) {
            // Ambil data terbaru dari tabel traffics berdasarkan kolom Jalur
            $data = DB::table('traffics')
                ->where('Jalur', $jalur)
                ->orderByDesc('created_at')
                ->first();

            if ($data) {
                $jumlah = $data->jumlah_kendaraan;
                $durasi = $data->durasi_lampu_hijau;
                $durasiMerah = $totalSiklusMerah - $durasi;

                // Simpan jumlah kendaraan per jalur untuk logika jalur terpadat
                $jumlahPerJalur[$jalur] = $jumlah;

                // Kirim jumlah kendaraan ke Blynk
                $res1 = Http::get("http://blynk.cloud/external/api/update", [
                    'token' => $token,
                    $pinMap[$jalur]['jumlah_kendaraan_rt'] => $jumlah,
                ]);

                // Kirim durasi lampu hijau ke Blynk
                $res2 = Http::get("http://blynk.cloud/external/api/update", [
                    'token' => $token,
                    $pinMap[$jalur]['durasi_lampu_hijau'] => $durasi,
                ]);

                // Kirim durasi merah
                $res3 = Http::get("http://blynk.cloud/external/api/update", [
                    'token' => $token,
                    $pinMerahMap[$jalur] => $durasiMerah,
                ]);

                $hasil[] = [
                    'jalur' => $jalur,
                    'jumlah_kendaraan' => $res1->successful(),
                    'durasi_lampu_hijau' => $res2->successful(),
                    'durasi_lampu_merah' => $res3->successful(),
                ];
            } else {
                $hasil[] = [
                    'jalur' => $jalur,
                    'error' => 'Data tidak ditemukan'
                ];
            }
        }

        // Kirim Jalur Terpadat ke V4
        if (!empty($jumlahPerJalur)) {
            $jalurTerpadat = array_keys($jumlahPerJalur, max($jumlahPerJalur))[0];
            $labelTerpadat = "Jalur " . $jalurTerpadat;

            $res3 = Http::get("http://blynk.cloud/external/api/update", [
                'token' => $token,
                'V4' => $labelTerpadat,
            ]);

            $status_terpadat = $res3->successful();

        }

        return view('blynk.status', compact('hasil', 'status_terpadat'));
    }

    public function handle()
{
    Artisan::call('blynk:send');
    return redirect()->back()->with('success', 'Data berhasil dikirim ke Blynk.');
}
}
