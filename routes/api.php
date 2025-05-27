<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrafficLogController;

Route::post('/traffic-logs', [TrafficLogController::class, 'store']);