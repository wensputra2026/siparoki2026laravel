<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIndirectId;

class DesaKelurahan extends Model
{
    use HasIndirectId;

    protected $table = 'desa_kelurahan';
    protected $primaryKey = 'id_desa';
    protected $appends = ['hashid', 'iid'];
    protected $fillable = [
        'id_desa',
        'kecamatan_id',
        'kode_desa',
        'nama_desa',
        'tipe',
        'kode_pos',
        'status',
        'delete_reason',
        'is_deleted',
    ];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id', 'id_kecamatan');
    }
}
