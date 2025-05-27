<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrafficLog;
use Carbon\Carbon;

class TrafficLogController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();

        foreach ($data as $log) {
            TrafficLog::create([
                'lane' => $log['lane'],
                'vehicle_count' => $log['vehicle_count'],
                'recorded_at' => Carbon::now()
            ]);
        }

        return response()->json(['message' => 'Data stored successfully'], 201);
    }
}
