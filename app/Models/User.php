<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'role_id',
        'username',
        'email',
        'password',
        'nama_lengkap',
        'no_hp',
        'foto',
        'wilayah_id',
        'kapela_id',
        'kub_id',
        'umat_id',
        'last_login',
        'status',
        'maintenance_access',
        'created_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'last_login' => 'datetime',
            'status' => 'boolean',
            'is_deleted' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        // No-op for legacy schema without remember_token column
    }

    public function getRememberTokenName()
    {
        return '';
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function kapela()
    {
        return $this->belongsTo(Kapela::class);
    }

    public function kub()
    {
        return $this->belongsTo(Kub::class);
    }

    public function umat()
    {
        return $this->belongsTo(Umat::class);
    }

    /**
     * Check if user has specific role or belongs to role array.
     */
    public function hasRole(string|array $roles): bool
    {
        if ($this->role_id == 1 || $this->id == 1) {
            return true;
        }

        $rawSlug = strtolower(trim($this->role?->slug ?? $this->role?->nama_role ?? ''));
        $cleanSlug = str_replace(['_', '-', ' '], '', $rawSlug);

        // Superadmin and Pastor Paroki always have full access
        if (in_array($cleanSlug, ['superadmin', 'superadministrator', 'admin', 'administrator', 'pastor', 'pastorparoki'])) {
            return true;
        }

        $targetRoles = is_array($roles) ? $roles : [$roles];
        foreach ($targetRoles as $target) {
            $cleanTarget = str_replace(['_', '-', ' '], '', strtolower(trim($target)));
            if ($cleanSlug === $cleanTarget || str_contains($cleanSlug, $cleanTarget) || str_contains($cleanTarget, $cleanSlug)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(['superadmin', 'admin', 'pastor-paroki', 'administrator', 'pastor']);
    }

    public function isSekretariat(): bool
    {
        return $this->hasRole(['sekretariat', 'tata-usaha', 'sekretaris']);
    }

    public function isBendahara(): bool
    {
        return $this->hasRole(['bendahara', 'keuangan', 'kasir']);
    }

    public function isKetuaWilayah(): bool
    {
        return $this->hasRole(['ketua-wilayah', 'koordinator-wilayah']);
    }

    public function isKetuaLingkungan(): bool
    {
        return $this->hasRole(['ketua-lingkungan', 'ketua-kub', 'pengurus-kub']);
    }

    public function isKomsos(): bool
    {
        return $this->hasRole(['komsos', 'admin-website', 'media', 'publikasi']);
    }
}
