<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SliderBanner extends Model
{
    protected $table = 'slider_banner';

    protected $fillable = [
        'judul',
        'subjudul',
        'gambar',
        'link_url',
        'tombol_teks',
        'urutan',
        'status',
    ];
}
