<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilParoki extends Model
{
    protected $table = 'profil_paroki';

    protected $fillable = [
        'nama_paroki',
        'keuskupan',
        'keuskupan_id',
        'dekenat_id',
        'paroki_id',
        'pelindung',
        'alamat',
        'telepon',
        'email',
        'website',
        'pastor_paroki',
        'foto_pastor',
        'pastor_email',
        'logo',
    ];
}
