<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterUskup extends Model
{
    protected $table = 'master_uskup';
    protected $fillable = [
        'nama_uskup',
        'gelar_depan',
        'keuskupan',
        'jabatan',
        'no_hp',
        'foto',
        'keterangan',
        'status',
    ];
}
