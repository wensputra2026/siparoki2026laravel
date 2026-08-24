<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPastorParoki extends Model
{
    protected $table = 'riwayat_pastor_paroki';
    protected $primaryKey = 'id_riwayat_pastor';
    public $timestamps = false;
    protected $guarded = [];
    protected $appends = ['nama_lengkap_gelar'];

    public function getNamaLengkapGelarAttribute(): string
    {
        return MasterPastor::formatNama($this);
    }

    public function paroki()
    {
        return $this->belongsTo(Paroki::class, 'paroki_id', 'id_paroki');
    }
}
