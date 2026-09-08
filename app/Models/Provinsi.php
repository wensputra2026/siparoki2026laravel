<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIndirectId;

class Provinsi extends Model
{
    use HasIndirectId;

    protected $table = 'provinsi';
    protected $primaryKey = 'id_provinsi';
    protected $appends = ['hashid', 'iid'];
    protected $fillable = [
        'kode_provinsi',
        'nama_provinsi',
        'status',
        'delete_reason',
        'is_deleted',
    ];

    public function kabupatens()
    {
        return $this->hasMany(Kabupaten::class, 'provinsi_id', 'id_provinsi');
    }
}
