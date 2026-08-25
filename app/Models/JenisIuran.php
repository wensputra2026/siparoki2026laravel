<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisIuran extends Model
{
    protected $table = 'jenis_iuran';

    protected $fillable = [
        'kode_iuran',
        'nama_iuran',
        'kategori_iuran',
        'basis_penagihan',
        'nominal_default',
        'periode',
        'wajib',
        'status',
        'keterangan',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'nominal_default' => 'decimal:2',
            'wajib' => 'integer',
            'status' => 'integer',
            'is_deleted' => 'integer',
        ];
    }

    public function iurans()
    {
        return $this->hasMany(Iuran::class, 'jenis_iuran_id');
    }
}
