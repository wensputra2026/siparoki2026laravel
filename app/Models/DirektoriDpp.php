<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirektoriDpp extends Model
{
    protected $table = 'direktori_dpp';
    protected $primaryKey = 'id_dpp';
    protected $fillable = [
        'id_dpp',
        'paroki_id',
        'umat_id',
        'umat_peran_id',
        'nama_lengkap',
        'jabatan',
        'bidang',
        'periode_mulai',
        'periode_selesai',
        'no_hp',
        'email',
        'foto',
        'urutan',
        'status_aktif',
        'tampil_frontend',
        'keterangan',
        'delete_reason',
        'is_deleted',
    ];

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
        $val = $this->attributes['status_aktif'] ?? $this->attributes['status'] ?? 1;
        return ($val == 1 || $val === '1' || $val === 'Aktif') ? 'Aktif' : 'Nonaktif';
    }

    public function setStatusAttribute($value)
    {
        $isAktif = ($value === 'Aktif' || $value == 1 || $value === '1' || $value === true);
        $this->attributes['status_aktif'] = $isAktif ? 1 : 0;
        $this->attributes['status'] = $isAktif ? 'Aktif' : 'Nonaktif';
    }

    public function setStatusAktifAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['status_aktif'] = in_array(strtolower($value), ['aktif', 'active', '1', 'true'], true) ? 1 : 0;
        } else {
            $this->attributes['status_aktif'] = $value ? 1 : 0;
        }
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
