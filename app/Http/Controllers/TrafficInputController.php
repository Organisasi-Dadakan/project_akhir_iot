<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Traffic;
use Illuminate\Support\Facades\Log;

class TrafficInputController extends Controller
{
    public function store(Request $request)
    {
        $payload = $request->all();

        // Cek apakah datanya dibungkus dalam key 'data' atau langsung array
        $data = isset($payload['data']) ? $payload['data'] : $payload;

        // Pastikan datanya array
        if (!is_array($data)) {
            return response()->json(['message' => 'Format data tidak valid'], 400);
        }

        foreach ($data as $item) {
            // Validasi ringan
            if (
                isset($item['jalur']) &&
                isset($item['jumlah_kendaraan']) &&
                isset($item['durasi_lampu_hijau'])
            ) {
                Traffic::create([
                    'Jalur' => $item['jalur'],
                    'jumlah_kendaraan' => $item['jumlah_kendaraan'],
                    'durasi_lampu_hijau' => $item['durasi_lampu_hijau'],
                ]);
            }
        }

        return response()->json(['message' => 'Data berhasil disimpan'], 200);
    }

    public function storeFromEsp(Request $request)
    {
        try {
            $rawData = $request->getContent(); // misalnya "40,45,50,12,18,22"
            $parts = explode(',', trim($rawData));

            if (count($parts) !== 6) {
                return response()->json(['message' => 'Format data tidak valid, harus 6 nilai'], 400);
            }

            foreach ($parts as $value) {
                if (!is_numeric($value)) {
                    return response()->json(['message' => 'Semua nilai harus berupa angka'], 400);
                }
            }

            [$hijauA, $hijauB, $hijauC, $kendaraanA, $kendaraanB, $kendaraanC] = $parts;

            $data = [
                ['Jalur' => 'A', 'jumlah_kendaraan' => $kendaraanA, 'durasi_lampu_hijau' => $hijauA],
                ['Jalur' => 'B', 'jumlah_kendaraan' => $kendaraanB, 'durasi_lampu_hijau' => $hijauB],
                ['Jalur' => 'C', 'jumlah_kendaraan' => $kendaraanC, 'durasi_lampu_hijau' => $hijauC],
            ];

            foreach ($data as $entry) {
                Traffic::create($entry);
            }

            Log::info('Data lalu lintas masuk', $data);

            return response()->json(['message' => 'Data berhasil disimpan'], 200);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data ESP', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Terjadi kesalahan di server'], 500);
        }
    }
}
