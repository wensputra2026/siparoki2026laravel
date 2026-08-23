<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Pengumuman extends Model
{

    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'isi',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'slug',
        'ringkasan',
        'kategori',
        'gambar',
        'penulis',
        'tgl_mulai',
        'tgl_selesai',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'tgl_mulai' => 'date',
            'tgl_selesai' => 'date',
            'status' => 'boolean',
            'is_deleted' => 'boolean',
        ];
    }
}
