<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dekenat extends Model
{
    protected $table = 'dekenat';
    protected $primaryKey = 'id_dekenat';
    protected $fillable = [
        'keuskupan_id',
        'kode_dekenat',
        'nama_dekenat',
        'deken',
        'alamat',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'desa_id',
        'telepon',
        'email',
        'keterangan',
        'status',
        'delete_reason',
        'is_deleted',
    ];

    public function keuskupan()
    {
        return $this->belongsTo(Keuskupan::class, 'keuskupan_id', 'id_keuskupan');
    }

    public function parokis()
    {
        return $this->hasMany(Paroki::class, 'dekenat_id', 'id_dekenat');
    }

    public function kuasiParokis()
    {
        return $this->hasMany(KuasiParoki::class, 'dekenat_id', 'id_dekenat');
    }
}
