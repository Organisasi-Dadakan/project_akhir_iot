<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Traffic extends Model
{
    use HasFactory;

    // Jika tabelnya bernama 'traffics', tidak perlu override $table.
    // Kalau kamu pakai nama lain, aktifkan baris berikut:
    // protected $table = 'nama_tabel';

    protected $fillable = [
        'Jalur',
        'jumlah_kendaraan',
        'durasi_lampu_hijau',
    ];

    // Optional: jika kamu tidak ingin timestamps otomatis
    // public $timestamps = false;
}
