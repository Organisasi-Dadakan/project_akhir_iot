<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JalurA;
use App\Models\JalurB;
use App\Models\JalurC;
use Illuminate\Support\Facades\DB;

class TrafficInputController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->input('data');

        if (!$data || !is_array($data)) {
            return response()->json(['message' => 'Format data tidak valid'], 400);
        }

        $inserted = [];

        foreach (['A', 'B', 'C'] as $jalur) {
            if (!isset($data[$jalur])) continue;

            $payload = $data[$jalur];

            $table = 'jalur_' . strtolower($jalur);

            $row = DB::table($table)->insertGetId([
                'jumlah_kendaraan' => $payload['jumlah_kendaraan'] ?? 0,
                'durasi_lampu_hijau' => $payload['durasi_lampu_hijau'] ?? 0,
                'timestamp' => $payload['timestamp'] ?? now(),
            ]);

            $inserted[$jalur] = DB::table($table)->where('id', $row)->first();
        }

        return response()->json([
            'message' => 'Data semua jalur berhasil disimpan',
            'data' => $inserted
        ]);
    }
}
