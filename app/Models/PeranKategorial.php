<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeranKategorial extends Model
{
    protected $table = 'peran_kategorial';
    protected $primaryKey = 'id_peran_kategorial';
    protected $fillable = [
        'id_peran_kategorial',
        'paroki_id',
        'kode_peran',
        'nama_peran',
        'slug',
        'kategori',
        'deskripsi',
        'koordinator_umat_id',
        'nama_koordinator',
        'no_hp_koordinator',
        'email_koordinator',
        'lokasi_kegiatan',
        'jadwal_kegiatan',
        'foto',
        'ikon',
        'warna_label',
        'status',
        'tampil_frontend',
        'urutan',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'delete_reason',
        'is_deleted',
    ];
}
