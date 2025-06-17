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

        DB::beginTransaction();

        try {
            foreach ($data as $item) {
                $validator = Validator::make($item, [
                    'jalur' => 'required|string|max:255',
                    'jumlah_kendaraan' => 'required|integer',
                    'durasi_lampu_hijau' => 'required|integer',
                ]);

                if ($validator->fails()) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Data item tidak valid',
                        'errors' => $validator->errors()
                    ], 422);
                }

                Traffic::create([
                    'jalur' => $item['jalur'],
                    'jumlah_kendaraan' => $item['jumlah_kendaraan'],
                    'durasi_lampu_hijau' => $item['durasi_lampu_hijau'],
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Data berhasil disimpan'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan data traffic: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan pada server'], 500);
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
