<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecuritySettings extends Model
{
    protected $table = 'security_settings';

    protected $fillable = [
        'setting_key',
        'setting_value',
    ];
}
