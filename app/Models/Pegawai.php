<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai'; // karena bukan jamak (pegawais)

    // Primary key is 'nip' (string), not an auto-incrementing integer
    protected $primaryKey = 'nip';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'divisi_id',
        'users_id',
        'nip',
        'nama_pegawai',
        'jabatan',
        'foto_wajah_asli',
    ];
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
