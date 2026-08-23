<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nama_role',
        'slug',
        'deskripsi',
        'permissions',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
