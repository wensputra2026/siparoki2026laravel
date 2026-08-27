<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatMutasiUmat extends Model
{
    use HasFactory;

    protected $table = 'riwayat_mutasi_umat';
    protected $guarded = [];

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
