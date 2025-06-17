<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Traffic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TrafficInputController extends Controller
{
    public function store(Request $request)
    {
        $payload = $request->all();
        $data = isset($payload['data']) ? $payload['data'] : $payload;

        if (!is_array($data) || empty($data)) {
            return response()->json(['message' => 'Format data tidak valid atau data kosong'], 400);
        }

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            foreach ($data as $item) {
                // Gunakan Validator Laravel
                $validator = Validator::make($item, [
                    'jalur' => 'required|string|max:255',
                    'jumlah_kendaraan' => 'required|integer',
                    'durasi_lampu_hijau' => 'required|integer',
                ]);

                // Jika validasi untuk satu item gagal, batalkan semua dan kirim error
                if ($validator->fails()) {
                    DB::rollBack(); // Batalkan transaksi
                    return response()->json([
                        'message' => 'Data item tidak valid',
                        'errors' => $validator->errors()
                    ], 422);
                }

                // Buat data baru, pastikan key sama dengan properti $fillable
                Traffic::create([
                    'Jalur' => $item['jalur'],
                    'jumlah_kendaraan' => $item['jumlah_kendaraan'],
                    'durasi_lampu_hijau' => $item['durasi_lampu_hijau'],
                ]);
            }

            // Jika semua item di dalam loop berhasil, commit transaksi
            DB::commit();

            return response()->json(['message' => 'Data berhasil disimpan'], 200);

        } catch (\Exception $e) {
            // Jika ada error lain (misal: koneksi db putus), batalkan transaksi
            DB::rollBack();

            // Log error untuk debugging
            Log::error('Gagal menyimpan data traffic: ' . $e->getMessage());

            return response()->json(['message' => 'Terjadi kesalahan pada server'], 500);
        }
    }
}


// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\Models\Traffic;

// class TrafficInputController extends Controller
// {
//     public function store(Request $request)
//     {
//         $payload = $request->all();

//         // Cek apakah datanya dibungkus dalam key 'data' atau langsung array
//         $data = isset($payload['data']) ? $payload['data'] : $payload;

//         // Pastikan datanya array
//         if (!is_array($data)) {
//             return response()->json(['message' => 'Format data tidak valid'], 400);
//         }

//         foreach ($data as $item) {
//             // Validasi ringan
//             if (
//                 isset($item['jalur']) &&
//                 isset($item['jumlah_kendaraan']) &&
//                 isset($item['durasi_lampu_hijau'])
//             ) {
//                 Traffic::create([
//                     'Jalur' => $item['jalur'],
//                     'jumlah_kendaraan' => $item['jumlah_kendaraan'],
//                     'durasi_lampu_hijau' => $item['durasi_lampu_hijau'],
//                 ]);
//             }
//         }

//         return response()->json(['message' => 'Data berhasil disimpan'], 200);
//     }
// }
