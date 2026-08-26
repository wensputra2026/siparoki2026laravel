<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirektoriDpp extends Model
{
    protected $table = 'direktori_dpp';
    protected $primaryKey = 'id_dpp';
    protected $guarded = [];

    protected $appends = ['seksi', 'status', 'nama', 'periode'];

    public function getSeksiAttribute()
    {
        return $this->attributes['bidang'] ?? $this->attributes['seksi'] ?? '';
    }

    public function setSeksiAttribute($value)
    {
        $this->attributes['bidang'] = $value;
        $this->attributes['seksi'] = $value;
    }

    public function getStatusAttribute()
    {
        return $this->attributes['status_aktif'] ?? $this->attributes['status'] ?? 'Aktif';
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status_aktif'] = $value;
        $this->attributes['status'] = $value;
    }

    public function getNamaAttribute()
    {
        return $this->attributes['nama_lengkap'] ?? $this->attributes['nama'] ?? '';
    }

    public function setNamaAttribute($value)
    {
        $this->attributes['nama_lengkap'] = $value;
        $this->attributes['nama'] = $value;
    }

    public function getPeriodeAttribute()
    {
        if (!empty($this->attributes['periode'])) {
            return $this->attributes['periode'];
        }
        $start = $this->attributes['periode_mulai'] ?? null;
        $end = $this->attributes['periode_selesai'] ?? null;
        if ($start && $end) {
            return "{$start} - {$end}";
        }
        return $start ?: ($end ?: '2024 - 2027');
    }

    public function setPeriodeAttribute($value)
    {
        $this->attributes['periode'] = $value;
        if (str_contains((string)$value, '-')) {
            $parts = explode('-', (string)$value);
            $this->attributes['periode_mulai'] = trim($parts[0] ?? '');
            $this->attributes['periode_selesai'] = trim($parts[1] ?? '');
        }
    }
}
