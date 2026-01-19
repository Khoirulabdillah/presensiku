<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeSetting extends Model
{
    protected $table = 'office_settings';

    protected $fillable = [
        'latitude',
        'longitude',
        'radius',
        'jam_masuk',
        'jam_pulang',
        'batas_awal_masuk',
        'toleransi_terlambat',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
}