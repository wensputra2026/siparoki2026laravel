<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggotaKategorial extends Model
{
    protected $table = 'anggota_kategorial';
    protected $primaryKey = 'id_anggota_kategorial';
    protected $fillable = [
        'id_anggota_kategorial',
        'peran_kategorial_id',
        'umat_id',
        'umat_peran_id',
        'kk_id',
        'paroki_id',
        'stasi_kapela_id',
        'wilayah_id',
        'lingkungan_id',
        'kub_id',
        'nama_anggota',
        'jabatan_dalam_kelompok',
        'tanggal_bergabung',
        'tanggal_keluar',
        'status_keanggotaan',
        'alasan_keluar',
        'keterangan',
        'delete_reason',
        'is_deleted',
        'foto',
    ];

    public function peranKategorial()
    {
        return $this->belongsTo(PeranKategorial::class, 'peran_kategorial_id', 'id_peran_kategorial');
    }
}
