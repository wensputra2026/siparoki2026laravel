<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIndirectId;
use App\Models\Concerns\HasUuid;

class KkKatolik extends Model
{
    use HasIndirectId, HasUuid;

    protected $table = 'kk_katolik';

    protected $fillable = [
        'uuid',
        'no_kk_kw',
        'no_kk_dukcapil',
        'nik_pemilik',
        'gereja_paroki',
        'lokasi_gereja',
        'wilayah_id',
        'kapela_id',
        'lingkungan_id',
        'kub_id',
        'nama_baptis_pemilik',
        'nama_lahir_pemilik',
        'nama_pasangan',
        'alamat_sekarang',
        'status_kepemilikan_rumah',
        'kategori_ekonomi',
        'bantuan_pastoral',
        'pekerjaan',
        'pendidikan',
        'golongan_darah',
        'penghasilan',
        'rt',
        'rw',
        'provinsi',
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

    protected $hidden = [
        'is_deleted',
    ];

    protected static function booted()
    {
        static::saving(function ($kk) {
            foreach (['nama_lahir_pemilik', 'nama_baptis_pemilik', 'nama_pasangan'] as $field) {
                if (!empty($kk->{$field})) {
                    $kk->{$field} = mb_convert_case(mb_strtolower(trim($kk->{$field}), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                }
            }
        });
    }

    public function getMaskedNikAttribute(): ?string
    {
        $nik = $this->nik_pemilik;
        if (!$nik) return null;
        $len = strlen($nik);
        if ($len <= 8) return str_repeat('*', $len);
        return substr($nik, 0, 4) . str_repeat('*', max(0, $len - 8)) . substr($nik, -4);
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function kapela()
    {
        return $this->belongsTo(Kapela::class);
    }

    public function kub()
    {
        return $this->belongsTo(Kub::class);
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
