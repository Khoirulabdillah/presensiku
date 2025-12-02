<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Izin extends Model
{
    protected $fillable = [
        'nip',
        'jenis_izin',
        'status_izin',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'bukti_path',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }
}
