<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Galeri extends Model
{

    protected $table = 'galeri';

    protected $fillable = [
        'judul',
        'slug',
        'deskripsi',
        'gambar',
        'album',
        'tanggal',
        'status',
        'youtube_url',
        'youtube_id',
        'youtube_thumbnail',
        'tipe',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'status' => 'boolean',
            'is_deleted' => 'boolean',
        ];
    }
}
