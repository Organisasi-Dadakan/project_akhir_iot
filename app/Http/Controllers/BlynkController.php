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

        $hasil = [];

        foreach ($jalurList as $jalur) {
            // Ambil data terbaru dari tabel traffics berdasarkan kolom Jalur
            $data = DB::table('traffics')
                ->where('Jalur', $jalur)
                ->orderByDesc('created_at')
                ->first();

            if ($data) {
                $jumlah = $data->jumlah_kendaraan;
                $durasi = $data->durasi_lampu_hijau;

                $res1 = Http::get("http://blynk.cloud/external/api/update", [
                    'token' => $token,
                    $pinMap[$jalur]['jumlah_kendaraan_rt'] => $jumlah,
                ]);

                $res2 = Http::get("http://blynk.cloud/external/api/update", [
                    'token' => $token,
                    $pinMap[$jalur]['durasi_lampu_hijau'] => $durasi,
                ]);

                $hasil[] = [
                    'jalur' => $jalur,
                    'jumlah_kendaraan' => $res1->successful(),
                    'durasi_lampu_hijau' => $res2->successful(),
                ];
            } else {
                $hasil[] = [
                    'jalur' => $jalur,
                    'error' => 'Data tidak ditemukan'
                ];
            }
        }

        return view('blynk.status', compact('hasil'));
    }

    public function handle()
{
    Artisan::call('blynk:send');
    return redirect()->back()->with('success', 'Data berhasil dikirim ke Blynk.');
}
}
