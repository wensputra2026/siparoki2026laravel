<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriKonten extends Model
{
    protected $table = 'kategori_konten';

    protected $fillable = [
        'nama_kategori',
        'slug',
        'deskripsi',
        'ikon',
        'urutan',
        'status',
    ];

    public function kontens()
    {
        return $this->hasMany(Konten::class, 'kategori_id');
    }
}
