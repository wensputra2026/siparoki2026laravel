<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIndirectId;
use App\Models\Concerns\HasUuid;

class Umat extends Model
{
    use HasIndirectId, HasUuid;

    protected $table = 'umat';

    protected $fillable = [
        'uuid',
        'niu',
        'kk_id',
        'no_urut_anggota',
        'nik',
        'nama_lengkap',
        'no_kk_kw',
        'nama_pemilik_kk',
        'nama_baptis',
        'nama_lahir',
        'nama_marga',
        'kode_anggota',
        'suku_etnis',
        'jenis_kelamin',
        'suku',
        'tanggal_lahir',
        'tempat_lahir',
        'hubungan_keluarga',
        'anak_ke',
        'agama_saat_ini',
        'agama_asal',
        'agama_sebelum_katolik',
        'status_menikah',
        'status_perkawinan',
        'status_perkawinan_kanonik',
        'kewarganegaraan',
        'status_tinggal',
        'handphone',
        'email',
        'pendidikan',
        'pendidikan_saat_ini',
        'pekerjaan',
        'golongan_darah',
        'talenta',
        'disabilitas',
        'status_baptis',
        'jenis_penerimaan_baptis',
        'tgl_baptis',
        'paroki_baptis',
        'pastor_baptis',
        'wali_baptis',
        'buku_baptis_vol',
        'buku_baptis_hal',
        'buku_baptis_no',
        'tgl_komuni_1',
        'paroki_komuni_1',
        'tgl_krisma',
        'paroki_krisma',
        'tgl_perkawinan',
        'paroki_perkawinan',
        'nama_pasangan',
        'peristiwa_lain',
        'no_surat_peristiwa',
        'status_panggilan',
        'nama_ordo_kongregasi',
        'tahap_panggilan',
        'tempat_tugas_biara',
        'tgl_tahbisan_kaul',
        'status_aktif',
        'status_umat',
        'pasangan_umat_id',
        'foto',
        'wilayah_id',
        'kub_id',
        'lingkungan_id',
        'kapela_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date:Y-m-d',
            'tgl_baptis' => 'date:Y-m-d',
            'tgl_komuni_1' => 'date:Y-m-d',
            'tgl_krisma' => 'date:Y-m-d',
            'tgl_perkawinan' => 'date:Y-m-d',
            'tgl_tahbisan_kaul' => 'date:Y-m-d',
            'status_aktif' => 'boolean',
            'is_deleted' => 'boolean',
        ];
    }

    protected $hidden = [
        'is_deleted',
    ];

    protected $appends = [
        'usia',
    ];

    public function getUsiaAttribute(): ?int
    {
        if (!$this->tanggal_lahir) return null;
        try {
            return \Carbon\Carbon::parse($this->tanggal_lahir)->age;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getMaskedNikAttribute(): ?string
    {
        if (!$this->nik) return null;
        $len = strlen($this->nik);
        if ($len <= 8) return str_repeat('*', $len);
        return substr($this->nik, 0, 4) . str_repeat('*', max(0, $len - 8)) . substr($this->nik, -4);
    }

    public function kk()
    {
        return $this->belongsTo(KkKatolik::class, 'kk_id');
    }

    public function lingkungan()
    {
        return $this->belongsTo(Lingkungan::class, 'lingkungan_id');
    }

    public function kub()
    {
        return $this->belongsTo(Kub::class, 'kub_id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function kapela()
    {
        return $this->belongsTo(Kapela::class, 'kapela_id');
    }

    public function getEffectiveLingkunganAttribute()
    {
        return $this->lingkungan ?? $this->kk?->lingkungan;
    }

    public function getEffectiveKubAttribute()
    {
        return $this->kub ?? $this->kk?->kub;
    }

    public function getEffectiveWilayahAttribute()
    {
        return $this->wilayah ?? $this->kk?->wilayah;
    }

    public function sakramen()
    {
        return $this->hasMany(Sakramen::class, 'id_umat');
    }

    public function sakramenUmat()
    {
        return $this->hasOne(SakramenUmat::class, 'umat_id');
    }

    public function pengajuanSakramen()
    {
        return $this->hasMany(PengajuanSakramen::class, 'umat_id');
    }
}
