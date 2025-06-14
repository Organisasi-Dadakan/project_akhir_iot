<?php

use App\Http\Controllers\TrafficInputController;
use Illuminate\Support\Facades\Route;

Route::post('/traffic/store', [TrafficInputController::class, 'store']);