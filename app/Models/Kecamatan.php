<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';
    protected $primaryKey = 'id_kecamatan';
    protected $fillable = [
        'kabupaten_id',
        'kode_kecamatan',
        'nama_kecamatan',
        'status',
        'delete_reason',
        'is_deleted',
    ];

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'id_kabupaten');
    }

    public function desas()
    {
        return $this->hasMany(DesaKelurahan::class, 'kecamatan_id', 'id_kecamatan');
    }
}
