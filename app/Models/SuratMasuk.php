<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class SuratMasuk extends Model
{

    protected $table = 'surat_masuk';

    protected $fillable = [
        'nomor_agenda',
        'nomor_surat',
        'tanggal_surat',
        'tanggal_diterima',
        'pengirim',
        'perihal',
        'lampiran',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_diterima' => 'date',
        ];
    }
}
