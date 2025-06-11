<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JalurB extends Model
{
    protected $table = 'jalur_b';
    public $timestamps = false;

    protected $fillable = [
        'jumlah_kendaraan',
        'durasi_lampu_hijau',
        'timestamp',
    ];
}
