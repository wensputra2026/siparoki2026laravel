<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirektoriKatekis extends Model
{
    protected $table = 'direktori_katekis';
    protected $primaryKey = 'id_katekis';
    protected $guarded = [];

    protected $appends = ['nama', 'status', 'kontak'];

    public function getNamaAttribute()
    {
        return $this->attributes['nama_lengkap'] ?? $this->attributes['nama'] ?? '';
    }

    public function setNamaAttribute($value)
    {
        $this->attributes['nama_lengkap'] = $value;
        $this->attributes['nama'] = $value;
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

    public function getKontakAttribute()
    {
        return $this->attributes['no_hp'] ?? $this->attributes['kontak'] ?? '';
    }

    public function setKontakAttribute($value)
    {
        $this->attributes['no_hp'] = $value;
        $this->attributes['kontak'] = $value;
    }
}
