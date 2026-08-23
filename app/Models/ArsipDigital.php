<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ArsipDigital extends Model
{

    protected $table = 'arsip_digital';

    protected $fillable = [
        'judul',
        'kategori_arsip',
        'deskripsi',
        'file_path',
        'ukuran_file',
        'hak_akses',
        'privacy_level',
    ];
}
