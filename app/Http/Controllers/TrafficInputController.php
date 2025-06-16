<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Traffic;

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
}
