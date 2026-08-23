<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class KkKatolik extends Model
{

    protected $table = 'kk_katolik';

    protected $fillable = [
        'no_kk_kw',
        'no_kk_dukcapil',
        'nik_pemilik',
        'gereja_paroki',
        'lokasi_gereja',
        'wilayah_id',
        'kapela_id',
        'lingkungan_id',
        'nama_baptis_pemilik',
        'nama_lahir_pemilik',
        'nama_pasangan',
        'alamat_sekarang',
        'rt',
        'rw',
        'desa_kelurahan',
        'kecamatan',
        'kota_kabupaten',
        'handphone',
        'email',
        'status_verifikasi',
        'status_kk',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_deleted' => 'boolean',
        ];
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function kapela()
    {
        return $this->belongsTo(Kapela::class);
    }

    public function lingkungan()
    {
        return $this->belongsTo(Lingkungan::class);
    }

    public function anggota()
    {
        return $this->hasMany(Umat::class, 'kk_id');
    }
}
