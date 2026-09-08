<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIndirectId;

class Kecamatan extends Model
{
    use HasIndirectId;

    protected $table = 'kecamatan';
    protected $primaryKey = 'id_kecamatan';
    protected $appends = ['hashid', 'iid'];
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
