<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPetugasLiturgi extends Model
{
    protected $table = 'jadwal_petugas_liturgi';
    protected $primaryKey = 'id_petugas_liturgi';
    protected $fillable = [
        'id_petugas_liturgi',
        'jadwal_misa_id',
        'umat_id',
        'nama_petugas',
        'jenis_tugas',
        'kelompok',
        'keterangan',
    ];
}
