<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keuskupan extends Model
{
    protected $table = 'keuskupan';
    protected $primaryKey = 'id_keuskupan';
    protected $guarded = [];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'id_provinsi');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'id_kabupaten');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id', 'id_kecamatan');
    }

    public function desa()
    {
        return $this->belongsTo(DesaKelurahan::class, 'desa_id', 'id_desa');
    }

    public function dekenats()
    {
        return $this->hasMany(Dekenat::class, 'keuskupan_id', 'id_keuskupan');
    }

    public function parokis()
    {
        return $this->hasMany(Paroki::class, 'keuskupan_id', 'id_keuskupan');
    }
}
