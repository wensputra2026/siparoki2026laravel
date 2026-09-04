<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Paroki extends Model
{

    protected $table = 'paroki';
    protected $primaryKey = 'id_paroki';
    protected $appends = ['logo_url'];

    protected $fillable = [
        'keuskupan_id',
        'dekenat_id',
        'kode_paroki',
        'nama_paroki',
        'pelindung_paroki',
        'status_paroki',
        'tanggal_berdiri',
        'nama_pastor_paroki_aktif',
        'foto_pastor',
        'nama_pastor_rekan',
        'alamat',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'desa_id',
        'telepon',
        'whatsapp',
        'email',
        'website',
        'logo',
        'banner',
        'maps_embed',
        'maps_url',
        'latitude',
        'longitude',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_berdiri' => 'date',
            'is_deleted' => 'boolean',
        ];
    }

    public function keuskupan()
    {
        return $this->belongsTo(Keuskupan::class, 'keuskupan_id', 'id_keuskupan');
    }

    public function dekenat()
    {
        return $this->belongsTo(Dekenat::class, 'dekenat_id', 'id_dekenat');
    }

    public function kevikepan()
    {
        return $this->belongsTo(Kevikepan::class, 'dekenat_id', 'id');
    }

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

    public function kapelas()
    {
        return $this->hasMany(Kapela::class, 'paroki_id');
    }

    public function stasis()
    {
        return $this->hasMany(Kapela::class, 'paroki_id');
    }

    public function wilayahs()
    {
        return $this->hasMany(Wilayah::class, 'paroki_id');
    }

    public function kubs()
    {
        return $this->hasMany(Kub::class, 'paroki_id');
    }

    public function kuasiParokis()
    {
        return $this->hasMany(KuasiParoki::class, 'paroki_id');
    }

    /**
     * URL logo paroki. Bila belum diupload atau file tidak ditemukan,
     * kembalikan logo default agar tidak muncul broken image.
     */
    public function getLogoUrlAttribute(): string
    {
        $default = asset('images/logo-paroki.png');

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
}
