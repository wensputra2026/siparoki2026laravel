<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aset extends Model
{
    protected $table = 'aset';

    protected $fillable = [
        'kode_aset',
        'nama_aset',
        'kategori',
        'kategori_aset_id',
        'jumlah',
        'satuan',
        'nilai_perolehan',
        'kondisi',
        'lokasi',
        'penanggung_jawab',
        'tanggal_perolehan',
        'status_aset',
        'level_pemilik',
        'foto',
        'deskripsi',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'nilai_perolehan' => 'decimal:2',
            'tanggal_perolehan' => 'date',
        ];
    }
}
