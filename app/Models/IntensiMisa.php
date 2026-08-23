<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntensiMisa extends Model
{
    protected $table = 'intensi_misa';

    protected $fillable = [
        'nama_pemohon',
        'kategori_intensi',
        'deskripsi',
        'tanggal_misa',
        'nominal_stipendium',
        'status_pembayaran',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_misa' => 'date',
            'nominal_stipendium' => 'decimal:2',
        ];
    }
}
