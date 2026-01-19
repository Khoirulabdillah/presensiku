<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $table = 'presensi';

    protected $fillable = [
        'nip',
        'tanggal_presensi',
        'jam_masuk',
        'jam_pulang',
        'foto_masuk',
        'foto_pulang',
        'type',
        'latitude',
        'longitude',
        'status',
        'late_minutes',
        'early_minutes',
    ];

    protected $casts = [
        'tanggal_presensi' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'late_minutes' => 'integer',
        'early_minutes' => 'integer',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
