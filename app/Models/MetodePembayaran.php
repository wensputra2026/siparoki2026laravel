<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodePembayaran extends Model
{
    protected $table = 'metode_pembayaran';

    protected $fillable = [
        'nama_bank',
        'nomor_rekening',
        'atas_nama',
        'logo_bank',
        'gambar_qris',
        'tipe',
        'urutan',
        'status',
        'petunjuk',
    ];
}
