<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BlynkController extends Controller
{
    public function status()
    {
        return $this->kirim();
    }

    public function kirim()
    {
        $token = env('BLYNK_TOKEN');
        $blynkUrl = 'http://blynk.cloud/external/api'; // ✅ Simpan base URL

        $jalurList = ['A', 'B', 'C'];
        $pinMap = [
            'A' => ['jumlah_kendaraan_rt' => 'V0', 'durasi_lampu_hijau' => 'V5'],
            'B' => ['jumlah_kendaraan_rt' => 'V1', 'durasi_lampu_hijau' => 'V6'],
            'C' => ['jumlah_kendaraan_rt' => 'V2', 'durasi_lampu_hijau' => 'V7'],
        ];
        $pinMerahMap = ['A' => 'V8', 'B' => 'V9', 'C' => 'V10'];
        $pinChartMap = ['A' => 'V11', 'B' => 'V12', 'C' => 'V13'];

        $hasil = [];
        $jumlahPerJalur = [];
        $totalSiklusMerah = 120;
        
        // ✅ [OPTIMASI] Siapkan data untuk batch update dalam satu array
        $batchData = ['token' => $token];
        
        // ✅ [OPTIMASI] Ambil semua data terbaru dari setiap jalur dalam SATU KALI query
        $latestDataPerJalur = DB::table('traffics')
            ->whereIn('jalur', $jalurList)
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('traffics')
                    ->groupBy('jalur');
            })
            ->get()
            ->keyBy('jalur'); // keyBy('jalur') agar mudah diakses dengan $data['A']

        // 🔵 Kirim jumlah kendaraan per jam ke V3
        $now = Carbon::now('Asia/Jakarta');
        $jumlahKendaraanPerJam = DB::table('traffics')
            ->whereBetween('created_at', [$now->copy()->startOfHour(), $now->copy()->endOfHour()])
            ->sum('jumlah_kendaraan');
        
        // Tambahkan data V3 ke batch
        $batchData['V3'] = $jumlahKendaraanPerJam;
        
        $hasil[] = ['jalur' => 'Semua Jalur', /* ... data lain ... */];

        // 🔁 Loop tiap jalur
        foreach ($jalurList as $jalur) {
            // Akses data dari koleksi yang sudah diambil, bukan query baru
            if (isset($latestDataPerJalur[$jalur])) {
                $data = $latestDataPerJalur[$jalur];
                $jumlah = $data->jumlah_kendaraan;
                $durasi = $data->durasi_lampu_hijau;
                
                $jumlahPerJalur[$jalur] = $jumlah;

                // ✅ [OPTIMASI] Kumpulkan data ke array batch, jangan kirim dulu
                $batchData[$pinMap[$jalur]['jumlah_kendaraan_rt']] = $jumlah;
                $batchData[$pinMap[$jalur]['durasi_lampu_hijau']] = $durasi;
                $batchData[$pinMerahMap[$jalur]] = $totalSiklusMerah - $durasi;

                //  चार्ट (Chart) API tetap dikirim terpisah karena formatnya beda
                $resChart = Http::get("$blynkUrl/update", [
                    'token' => $token, 'pin' => $pinChartMap[$jalur], 'value' => $jumlah
                ]);

                $hasil[] = [
                    'jalur' => $jalur,
                    'is_success' => true, // Akan di-update setelah batch call
                    'jumlah_chart' => $resChart->successful(),
                ];
            } else {
                $hasil[] = ['jalur' => $jalur, 'error' => '❌ Tidak ada data untuk jalur ini'];
            }
        }

        // 🟥 Tentukan dan kirim jalur terpadat ke V4
        if (!empty($jumlahPerJalur)) {
            $labelTerpadat = 'Jalur ' . implode(', ', array_keys($jumlahPerJalur, max($jumlahPerJalur)));
            $batchData['V4'] = $labelTerpadat; // Tambahkan ke batch
        }
        
        // ✅ [OPTIMASI] Kirim SEMUA data dalam SATU KALI permintaan HTTP
        $batchResponse = Http::get("$blynkUrl/batch/update", $batchData);
        
        // Update status keberhasilan untuk view
        foreach ($hasil as $key => $item) {
            if (isset($item['is_success'])) {
                $hasil[$key]['batch_status'] = $batchResponse->successful();
            }
        }
        
        return view('blynk.status', [
            'hasil' => $hasil, 
            'status_terpadat' => $batchResponse->successful()
        ]);
    }

    public function cekDataBaru($lastTimestamp)
    {
        // ✅ [PERBAIKAN] Tambahkan pengecekan null untuk menghindari error jika tabel kosong
        $latest = DB::table('traffics')->orderByDesc('created_at')->first();
        if (!$latest) {
            return response()->json(['ada_data_baru' => false]);
        }

        Log::info("🔍 lastTimestamp frontend: $lastTimestamp");
        Log::info("🕒 created_at terbaru DB: " . $latest->created_at);

        $dataTerbaru = DB::table('traffics')
            ->where('created_at', '>', $lastTimestamp)
            ->exists();

        return response()->json(['ada_data_baru' => $dataTerbaru]);
    }
}

// namespace App\Http\Controllers;

// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Log;
// use Carbon\Carbon;

// class BlynkController extends Controller
// {
//     public function status()
//     {
//         // Jalankan method kirim dan langsung return view-nya
//         return $this->kirim();
//     }

//     public function kirim()
//     {
//         $token = env('BLYNK_TOKEN');

//         $jalurList = ['A', 'B', 'C'];

//         $pinMap = [
//             'A' => ['jumlah_kendaraan_rt' => 'V0', 'durasi_lampu_hijau' => 'V5'],
//             'B' => ['jumlah_kendaraan_rt' => 'V1', 'durasi_lampu_hijau' => 'V6'],
//             'C' => ['jumlah_kendaraan_rt' => 'V2', 'durasi_lampu_hijau' => 'V7'],
//         ];

//         $pinMerahMap = [
//             'A' => 'V8',
//             'B' => 'V9',
//             'C' => 'V10',
//         ];

//         $pinChartMap = [
//             'A' => 'V11',
//             'B' => 'V12',
//             'C' => 'V13',
//         ];

//         $hasil = [];
//         $jumlahPerJalur = [];
//         $status_terpadat = null;
//         $totalSiklusMerah = 120;

//         // 🔵 Kirim jumlah kendaraan per jam ke V3
//         $now = Carbon::now('Asia/Jakarta');
//         $start = $now->copy()->startOfHour();
//         $end = $now->copy()->endOfHour();

//         $jumlahKendaraanPerJam = DB::table('traffics')
//             ->whereBetween('created_at', [$start, $end])
//             ->sum('jumlah_kendaraan');

//         $resV3 = Http::get("http://blynk.cloud/external/api/update", [
//             'token' => $token,
//             'V3' => $jumlahKendaraanPerJam,
//         ]);

//         $hasil[] = [
//             'jalur' => 'Semua Jalur',
//             'jam' => $start->format('H:i') . ' - ' . $end->format('H:i'),
//             'jumlah_kendaraan_per_jam' => $jumlahKendaraanPerJam,
//             'status' => $resV3->successful(),
//         ];

//         // 🔁 Loop tiap jalur
//         foreach ($jalurList as $jalur) {
//             $data = DB::table('traffics')
//                 ->where('jalur', $jalur)
//                 ->orderByDesc('created_at')
//                 ->first();

//             if ($data) {
//                 $jumlah = $data->jumlah_kendaraan;
//                 $durasi = $data->durasi_lampu_hijau;
//                 $durasiMerah = $totalSiklusMerah - $durasi;

//                 $jumlahPerJalur[$jalur] = $jumlah;

//                 // Kirim jumlah kendaraan realtime
//                 $res1 = Http::get("http://blynk.cloud/external/api/update", [
//                     'token' => $token,
//                     $pinMap[$jalur]['jumlah_kendaraan_rt'] => $jumlah,
//                 ]);

//                 // Kirim durasi hijau
//                 $res2 = Http::get("http://blynk.cloud/external/api/update", [
//                     'token' => $token,
//                     $pinMap[$jalur]['durasi_lampu_hijau'] => $durasi,
//                 ]);

//                 // Kirim durasi merah
//                 $res3 = Http::get("http://blynk.cloud/external/api/update", [
//                     'token' => $token,
//                     $pinMerahMap[$jalur] => $durasiMerah,
//                 ]);

//                 // Kirim juga ke Chart
//                 $resChart = Http::get("http://blynk.cloud/external/api/update", [
//                     'token' => $token,
//                     'pin' => $pinChartMap[$jalur],
//                     'value' => $jumlah,
//                 ]);

//                 $hasil[] = [
//                     'jalur' => $jalur,
//                     'jumlah_kendaraan' => $res1->successful(),
//                     'durasi_lampu_hijau' => $res2->successful(),
//                     'durasi_lampu_merah' => $res3->successful(),
//                     'jumlah_chart' => $resChart->successful(),
//                 ];
//             } else {
//                 $hasil[] = [
//                     'jalur' => $jalur,
//                     'error' => '❌ Tidak ada data untuk jalur ini',
//                 ];
//             }
//         }

//         // 🟥 Kirim jalur terpadat ke V4
//         if (!empty($jumlahPerJalur)) {
//             $maxJumlah = max($jumlahPerJalur);
//             $jalurTerpadatList = array_keys($jumlahPerJalur, $maxJumlah);
//             $labelTerpadat = 'Jalur ' . implode(', ', $jalurTerpadatList);

//             $res4 = Http::get("http://blynk.cloud/external/api/update", [
//                 'token' => $token,
//                 'V4' => $labelTerpadat,
//             ]);

//             $status_terpadat = $res4->successful();
//         }

//         return view('blynk.status', compact('hasil', 'status_terpadat'));
//     }

//     public function cekDataBaru($lastTimestamp)
// {
//     $latest = DB::table('traffics')->orderByDesc('created_at')->first();

//     Log::info("🔍 lastTimestamp frontend: $lastTimestamp");
//     Log::info("🕒 created_at terbaru DB: " . ($latest->created_at ?? 'NULL'));

//     $dataTerbaru = DB::table('traffics')
//         ->where('created_at', '>', $lastTimestamp)
//         ->exists();

//     return response()->json(['ada_data_baru' => $dataTerbaru]);
// }

// }
