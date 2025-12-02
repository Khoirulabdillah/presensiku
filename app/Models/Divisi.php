<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    /**
     * Explicit table name because migration creates `divisi` (singular).
     */
    protected $table = 'divisi';
     protected $fillable = [
        'id',
        'nama_divisi',
    ];

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class, 'divisi_id');
    }
}
