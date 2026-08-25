<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanOtp extends Model
{
    protected $table = 'pengaturan_otp';

    protected $fillable = [
        'provider',
        'api_key',
        'sender_number',
        'device_id',
        'template_otp',
        'template_notifikasi',
        'status',
    ];
}
