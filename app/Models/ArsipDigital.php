<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipDigital extends Model
{
    protected $table = 'arsip_digital';

    protected $fillable = [
        'judul',
        'kategori_arsip',
        'nomor_arsip',
        'tahun_arsip',
        'deskripsi',
        'tanggal_arsip',
        'lokasi_penyimpanan',
        'hak_akses',
        'privacy_level',
        'paroki_id',
        'file_path',
        'file_type',
        'file_size',
        'download_count',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_deleted',
    ];

    /**
     * Alias accessors for backward compatibility
     */
    public function getNamaDokumenAttribute(): ?string
    {
        return $this->attributes['judul'] ?? null;
    }

    public function setNamaDokumenAttribute(?string $value): void
    {
        $this->attributes['judul'] = $value;
    }

    public function getKategoriAttribute(): ?string
    {
        return $this->attributes['kategori_arsip'] ?? null;
    }

    public function setKategoriAttribute(?string $value): void
    {
        $this->attributes['kategori_arsip'] = $value;
    }

    public function getTglArsipAttribute(): ?string
    {
        return $this->attributes['tanggal_arsip'] ?? null;
    }

    public function setTglArsipAttribute(?string $value): void
    {
        $this->attributes['tanggal_arsip'] = $value;
    }

    public function getFileUrlAttribute(): ?string
    {
        $path = $this->attributes['file_path'] ?? null;
        if (!$path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $filename = basename($path);
        if (file_exists(public_path('assets/uploads/arsip/' . $filename))) {
            return asset('assets/uploads/arsip/' . $filename);
        }
        if (file_exists(public_path('uploads/arsip/' . $filename))) {
            return asset('uploads/arsip/' . $filename);
        }
        if (file_exists(public_path('storage/' . $path))) {
            return asset('storage/' . $path);
        }
        return asset($path);
    }
}
