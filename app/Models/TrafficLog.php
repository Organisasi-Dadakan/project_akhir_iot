<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrafficLog extends Model
{
    protected $fillable = ['lane', 'vehicle_count', 'recorded_at'];
    protected $casts = [
        'recorded_at' => 'datetime',
    ];
}
