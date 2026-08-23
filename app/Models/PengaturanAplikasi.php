<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanAplikasi extends Model
{
    protected $table = 'pengaturan_aplikasi';

    protected $primaryKey = 'id_pengaturan';

    public $incrementing = false;

    protected $fillable = [
        'id_pengaturan',
        'nama_aplikasi',
        'nama_paroki',
        'alamat_paroki',
        'telepon_paroki',
        'email_paroki',
        'logo',
        'favicon',
    ];
}
