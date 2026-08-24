<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggotaKategorial extends Model
{
    protected $table = 'anggota_kategorial';
    protected $primaryKey = 'id_anggota_kategorial';
    protected $guarded = [];

    public function peranKategorial()
    {
        return $this->belongsTo(PeranKategorial::class, 'peran_kategorial_id', 'id_peran_kategorial');
    }
}
