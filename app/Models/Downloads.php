<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Downloads extends Model
{
    protected $table = 'downloads';

    protected $fillable = [
        'judul',
        'kategori',
        'keterangan',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'download_count',
        'is_active',
        'status',
    ];

    protected $appends = [
        'nama_file',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'download_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getNamaFileAttribute(): ?string
    {
        return $this->attributes['judul'] ?? $this->attributes['file_name'] ?? null;
    }

    public function setNamaFileAttribute(?string $value): void
    {
        $this->attributes['judul'] = $value;
        if (empty($this->attributes['file_name'])) {
            $this->attributes['file_name'] = $value;
        }
    }

    public function getStatusAttribute(): string
    {
        if (isset($this->attributes['status']) && !empty($this->attributes['status'])) {
            return $this->attributes['status'];
        }
        return ($this->attributes['is_active'] ?? 1) ? 'Aktif' : 'Nonaktif';
    }

    public function setStatusAttribute($value): void
    {
        if (is_string($value)) {
            $this->attributes['is_active'] = (strcasecmp($value, 'Aktif') === 0 || $value === '1') ? 1 : 0;
            $this->attributes['status'] = $value;
        } else {
            $this->attributes['is_active'] = $value ? 1 : 0;
            $this->attributes['status'] = $value ? 'Aktif' : 'Nonaktif';
        }
    }
}
