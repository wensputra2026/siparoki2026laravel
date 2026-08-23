<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class LapakProduk extends Model
{

    protected $table = 'lapak_produk';

    protected $fillable = [
        'nama_produk',
        'deskripsi',
        'harga',
        'stok',
        'status_approval',
        'penjual',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'stok' => 'integer',
        ];
    }
}
