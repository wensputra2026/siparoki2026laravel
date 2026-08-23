<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SakramenUmat extends Model
{
    protected $table = 'sakramen_umat';

    protected $fillable = [
        'umat_id',
        'is_baptis',
        'tanggal_baptis',
        'no_surat_baptis',
        'liber_baptis',
        'halaman_baptis',
        'wali_baptis',
        'pastor_baptis',
        'paroki_terdaftar',
        'keuskupan_baptis',
        'gereja_paroki_baptis',
        'kota_lokasi_baptis',
        'is_komuni',
        'tgl_komuni_pertama',
        'no_komuni_pertama',
        'is_krisma',
        'tanggal_krisma',
        'no_surat_krisma',
        'is_nikah',
        'tanggal_menikah',
        'no_surat_nikah',
        'menikah_dengan',
        'yang_menikahkan',
    ];

    protected function casts(): array
    {
        return [
            'is_baptis' => 'boolean',
            'is_komuni' => 'boolean',
            'is_krisma' => 'boolean',
            'is_nikah' => 'boolean',
            'tanggal_baptis' => 'date',
            'tgl_komuni_pertama' => 'date',
            'tanggal_krisma' => 'date',
            'tanggal_menikah' => 'date',
        ];
    }

    public function umat()
    {
        return $this->belongsTo(Umat::class, 'umat_id');
    }
}
