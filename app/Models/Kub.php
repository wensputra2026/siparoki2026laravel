<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kub extends Model
{
    protected $table = 'kub';
    protected $fillable = [
        'paroki_id',
        'wilayah_id',
        'kapela_id',
        'lingkungan_id',
        'kode_kub',
        'nama_kub',
        'nama_pelindung',
        'ketua_kub',
        'deskripsi',
        'no_hp',
        'keterangan',
        'status',
        'delete_reason',
        'is_deleted',
    ];

    public function lingkungan()
    {
        return $this->belongsTo(Lingkungan::class, 'lingkungan_id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function paroki()
    {
        return $this->belongsTo(Paroki::class, 'paroki_id', 'id_paroki');
    }

    public function kapela()
    {
        return $this->belongsTo(Kapela::class, 'kapela_id');
    }
}
