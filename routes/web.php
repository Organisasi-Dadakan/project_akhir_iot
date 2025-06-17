<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BlynkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/blynk/status', [BlynkController::class, 'status']);
Route::get('/sendToBlynk', [BlynkController::class, 'kirim']);
Route::get('/sendToBlynk/{lastTimestamp}', [BlynkController::class, 'cekDataBaru']);

require __DIR__.'/auth.php';