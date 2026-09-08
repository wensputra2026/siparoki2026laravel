<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIndirectId;

class Kabupaten extends Model
{
    use HasIndirectId;

    protected $table = 'kabupaten';
    protected $primaryKey = 'id_kabupaten';
    protected $appends = ['hashid', 'iid'];
    protected $fillable = [
        'provinsi_id',
        'kode_kabupaten',
        'nama_kabupaten',
        'tipe',
        'status',
        'delete_reason',
        'is_deleted',
    ];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'id_provinsi');
    }

    public function kecamatans()
    {
        return $this->hasMany(Kecamatan::class, 'kabupaten_id', 'id_kabupaten');
    }
}
