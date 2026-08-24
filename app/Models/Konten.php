<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Konten extends Model
{

    protected $table = 'konten';

    protected $fillable = [
        'kategori_id',
        'kategori',
        'judul',
        'slug',
        'isi',
        'excerpt',
        'gambar',
        'file_pdf',
        'arsip_id',
        'arsip_digital_id',
        'embed_pdf',
        'tags',
        'tipe',
        'status_publish',
        'is_featured',
        'tanggal_publish',
        'penulis',
        'views',
        'meta_title',
        'meta_description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'embed_pdf' => 'boolean',
            'tanggal_publish' => 'datetime',
            'views' => 'integer',
            'is_deleted' => 'boolean',
        ];
    }
}
