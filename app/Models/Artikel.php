<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    protected $table = 'artikel';

    protected $fillable = [
        'judul',
        'slug',
        'ringkasan',
        'konten',
        'gambar',
        'kategori',
        'status',
        'tanggal_publish',
        'penulis_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_publish' => 'datetime',
        ];
    }
}
