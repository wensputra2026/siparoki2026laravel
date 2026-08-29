<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatPesan extends Model
{
    use HasFactory;

    protected $table = 'chat_pesan';

    protected $fillable = [
        'pengirim_id',
        'penerima_id',
        'pesan',
        'lampiran',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'pengirim_id' => 'integer',
        'penerima_id' => 'integer',
    ];

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }

    public function scopeBetweenUsers($query, $user1, $user2)
    {
        return $query->where(function ($q) use ($user1, $user2) {
            $q->where('pengirim_id', $user1)->where('penerima_id', $user2);
        })->orWhere(function ($q) use ($user1, $user2) {
            $q->where('pengirim_id', $user2)->where('penerima_id', $user1);
        });
    }
}
