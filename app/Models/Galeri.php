<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIndirectId;
use App\Models\Concerns\HasUuid;

class Galeri extends Model
{
    use HasIndirectId, HasUuid;

    protected $table = 'galeri';

    protected $fillable = [
        'uuid',
        'judul',
        'slug',
        'deskripsi',
        'gambar',
        'cover',
        'album',
        'kategori',
        'tanggal',
        'lokasi',
        'status',
        'status_publish',
        'urutan',
        'og_image',
        'youtube_url',
        'youtube_id',
        'youtube_thumbnail',
        'tipe',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'created_by',
        'updated_by',
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
