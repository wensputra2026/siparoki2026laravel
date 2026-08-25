<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    protected $table = 'security_logs';

    protected $fillable = [
        'ip_address',
        'user_id',
        'username',
        'event_type',
        'user_agent',
        'status',
        'details',
    ];
}
