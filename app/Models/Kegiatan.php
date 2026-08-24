<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Kegiatan extends Model
{

    protected $table = 'kegiatan';

    protected $fillable = [
        'judul',
        'nama_kegiatan',
        'slug',
        'kategori',
        'tanggal_mulai',
        'tanggal_selesai',
        'waktu',
        'lokasi',
        'penyelenggara',
        'gambar',
        'foto',
        'poster',
        'status',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }
}
