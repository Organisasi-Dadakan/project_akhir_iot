<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JalurC extends Model
{
    protected $table = 'jalur_c';
    public $timestamps = false;

    protected $fillable = [
        'jumlah_kendaraan',
        'durasi_lampu_hijau',
        'timestamp',
    ];
}
