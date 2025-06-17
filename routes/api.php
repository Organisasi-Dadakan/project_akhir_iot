<?php

use App\Http\Controllers\TrafficInputController;
use Illuminate\Support\Facades\Route;

Route::post('/traffic/store', [TrafficInputController::class, 'store']);
Route::post('/traffic/store2', [TrafficInputController::class, 'storeFromEsp']);