<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Aset extends Model
{

    protected $table = 'aset';

    protected $fillable = [
        'kode_aset',
        'kategori_aset_id',
        'nama_aset',
        'deskripsi',
        'kondisi',
        'status_aset',
        'level_pemilik',
        'lokasi',
        'nilai_perolehan',
        'tanggal_perolehan',
    ];

    protected function casts(): array
    {
        return [
            'nilai_perolehan' => 'decimal:2',
            'tanggal_perolehan' => 'date',
        ];
    }
}
