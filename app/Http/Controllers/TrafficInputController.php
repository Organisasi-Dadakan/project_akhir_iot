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
        try {
            $data = $request->json()->all();

            // Pastikan data berbentuk array
            if (!is_array($data)) {
                return response()->json([
                    'message' => 'Format JSON harus berupa array'
                ], 400);
            }

            $inserted = [];
            $errors = [];

            foreach ($data as $index => $item) {
                // Validasi tiap item
                $validator = Validator::make($item, [
                    'jalur' => 'required|string|in:A,B,C',
                    'jumlah_kendaraan' => 'required|integer|min:0',
                    'durasi_lampu_hijau' => 'required|integer|min:0',
                ]);

                if ($validator->fails()) {
                    $errors[$index] = $validator->errors()->all();
                    continue;
                }

                // Simpan ke DB
                $record = Traffic::create([
                    'Jalur' => $item['jalur'],
                    'jumlah_kendaraan' => $item['jumlah_kendaraan'],
                    'durasi_lampu_hijau' => $item['durasi_lampu_hijau'],
                ]);

                $inserted[] = $record;
            }

            return response()->json([
                'message' => 'Proses selesai',
                'inserted' => $inserted,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            Log::error('TrafficInput Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage(),
            ], 500);
        }
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
