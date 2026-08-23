<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class SuratKeluar extends Model
{

    protected $table = 'surat_keluar';

    protected $fillable = [
        'nomor_surat',
        'tanggal_surat',
        'tujuan',
        'perihal',
        'lampiran',
        'penandatangan',
        'status_draft',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
        ];
    }
}
