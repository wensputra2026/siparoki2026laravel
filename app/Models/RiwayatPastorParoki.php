<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPastorParoki extends Model
{
    protected $table = 'riwayat_pastor_paroki';
    protected $primaryKey = 'id_riwayat_pastor';
    public $timestamps = false;
    protected $fillable = [
        'id_riwayat_pastor',
        'pastor_id',
        'paroki_id',
        'jenis_tempat_tugas',
        'nama_tempat_tugas',
        'nama_pastor',
        'gelar',
        'tarekat',
        'status_imamat',
        'jabatan',
        'periode_mulai',
        'periode_selesai',
        'foto',
        'biografi_singkat',
        'catatan_pelayanan',
        'urutan',
        'tampil_frontend',
        'status',
        'delete_reason',
        'is_deleted',
        'status_pelayanan',
        'tahun_mulai',
        'tahun_selesai',
        'keterangan',
    ];
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
