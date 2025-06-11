<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrafficLogController;
use App\Http\Controllers\TrafficInputController;

Route::post('/traffic-logs', [TrafficLogController::class, 'store']);
Route::post('/traffic/store', [TrafficInputController::class, 'store']);