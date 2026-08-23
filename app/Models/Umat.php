<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Umat extends Model
{

    protected $table = 'umat';

    protected $fillable = [
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
        'jenis_kelamin',
        'suku',
        'tanggal_lahir',
        'tempat_lahir',
        'hubungan_keluarga',
        'anak_ke',
        'agama_saat_ini',
        'agama_sebelum_katolik',
        'status_menikah',
        'kewarganegaraan',
        'status_tinggal',
        'handphone',
        'email',
        'pendidikan_saat_ini',
        'pekerjaan',
        'golongan_darah',
        'status_aktif',
        'status_umat',
        'pasangan_umat_id',
        'foto',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'status_aktif' => 'boolean',
            'is_deleted' => 'boolean',
        ];
    }

    protected $hidden = [
        'is_deleted',
    ];

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
        return $this->belongsTo(Lingkungan::class);
    }

    public function sakramen()
    {
        return $this->hasMany(Sakramen::class, 'id_umat');
    }

    public function sakramenUmat()
    {
        return $this->hasOne(SakramenUmat::class, 'umat_id');
    }
}
