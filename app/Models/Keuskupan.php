<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keuskupan extends Model
{
    protected $table = 'keuskupan';
    protected $primaryKey = 'id_keuskupan';
    protected $appends = ['logo_url', 'uskup', 'no_telp'];
    protected $fillable = [
        'kode_keuskupan',
        'nama_keuskupan',
        'nama_latin',
        'nama_uskup',
        'uskup',
        'alamat',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'desa_id',
        'telepon',
        'no_telp',
        'email',
        'website',
        'logo',
        'keterangan',
        'status',
        'delete_reason',
        'is_deleted',
    ];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'id_provinsi');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'id_kabupaten');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id', 'id_kecamatan');
    }

    public function desa()
    {
        return $this->belongsTo(DesaKelurahan::class, 'desa_id', 'id_desa');
    }

    public function dekenats()
    {
        return $this->hasMany(Dekenat::class, 'keuskupan_id', 'id_keuskupan');
    }

    public function parokis()
    {
        return $this->hasMany(Paroki::class, 'keuskupan_id', 'id_keuskupan');
    }

    /**
     * URL logo keuskupan. Bila belum diupload atau file tidak ditemukan,
     * kembalikan logo default agar tidak muncul broken image.
     */
    public function getLogoUrlAttribute(): string
    {
        $default = asset('images/default-keuskupan.svg');

        if (!empty($this->attributes['logo'])) {
            $relative = ltrim((string) $this->attributes['logo'], '/');
            $relative = str_starts_with($relative, 'public/')
                ? substr($relative, 7)
                : $relative;

            if (file_exists(public_path($relative))) {
                return asset($relative);
            }
        }

        return $default;
    }

    public function getUskupAttribute(): ?string
    {
        return $this->attributes['nama_uskup'] ?? null;
    }

    public function setUskupAttribute($value): void
    {
        $this->attributes['nama_uskup'] = $value;
    }

    public function getNoTelpAttribute(): ?string
    {
        return $this->attributes['telepon'] ?? null;
    }

    public function setNoTelpAttribute($value): void
    {
        $this->attributes['telepon'] = $value;
    }
}
