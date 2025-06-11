<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JalurA extends Model
{
    protected $table = 'jalur_a';
    public $timestamps = false;

    protected $fillable = [
        'jumlah_kendaraan',
        'durasi_lampu_hijau',
        'timestamp',
    ];
}
