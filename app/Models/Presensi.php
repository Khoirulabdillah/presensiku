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
    ];

    protected $casts = [
        'tanggal_presensi' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
