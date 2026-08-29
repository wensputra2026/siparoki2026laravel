<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatMutasiUmat extends Model
{
    use HasFactory;

    protected $table = 'riwayat_mutasi_umat';
    protected $fillable = [
        'umat_id',
        'jenis_mutasi',
        'paroki_tujuan',
        'no_surat_pindah',
        'tgl_mutasi',
        'alasan',
        'status_sebelum',
        'status_sesudah',
        'paroki_asal_id',
        'paroki_asal_nama',
        'paroki_tujuan_id',
        'paroki_tujuan_nama',
        'wilayah_asal_id',
        'wilayah_tujuan_id',
        'kub_asal_id',
        'kub_tujuan_id',
        'alamat_sebelum',
        'alamat_sesudah',
        'tgl_surat_pindah',
        'kk_id',
    ];

    public function umat()
    {
        return $this->belongsTo(Umat::class, 'umat_id');
    }

    public function kk()
    {
        return $this->belongsTo(KkKatolik::class, 'kk_id');
    }

    public function kubAsal()
    {
        return $this->belongsTo(Kub::class, 'kub_asal_id');
    }

    public function kubTujuan()
    {
        return $this->belongsTo(Kub::class, 'kub_tujuan_id');
    }

    public function wilayahAsal()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_asal_id');
    }

    public function wilayahTujuan()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_tujuan_id');
    }

    public function parokiAsal()
    {
        return $this->belongsTo(Paroki::class, 'paroki_asal_id');
    }

    public function parokiTujuan()
    {
        return $this->belongsTo(Paroki::class, 'paroki_tujuan_id');
    }
}
