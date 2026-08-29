<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SambutanPastor extends Model
{
    protected $table = 'sambutan_pastor';
    protected $primaryKey = 'id_sambutan';
    protected $fillable = [
        'id_sambutan',
        'pastor_id',
        'nama_pastor',
        'jabatan_pastor',
        'foto_pastor',
        'judul_sambutan',
        'isi_sambutan',
        'kutipan_singkat',
        'tanda_tangan',
        'tanggal_sambutan',
        'status_publish',
        'tampil_beranda',
        'tampil_sidebar',
        'tampil_profil',
        'urutan',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'is_deleted',
        'delete_reason',
        'jabatan',
        'foto',
    ];
}
