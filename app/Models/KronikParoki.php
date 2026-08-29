<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KronikParoki extends Model
{
    protected $table = 'kronik_paroki';
    protected $primaryKey = 'id_kronik';
    protected $fillable = [
        'id_kronik',
        'paroki_id',
        'judul_kronik',
        'slug',
        'tanggal_peristiwa',
        'kategori_kronik',
        'lokasi_peristiwa',
        'isi_kronik',
        'ringkasan',
        'penulis',
        'sumber_data',
        'foto_utama',
        'dokumen_lampiran',
        'status_publish',
        'tampil_frontend',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'views',
        'delete_reason',
        'is_deleted',
    ];
}
