<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi_sistem';

    protected $fillable = [
        'user_id',
        'role_target',
        'kub_id',
        'wilayah_id',
        'kapela_id',
        'tipe',
        'judul',
        'pesan',
        'link',
        'data',
        'is_read',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kub()
    {
        return $this->belongsTo(Kub::class, 'kub_id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function kapela()
    {
        return $this->belongsTo(Kapela::class, 'kapela_id');
    }
}
