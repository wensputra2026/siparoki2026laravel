<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasName
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

    public function canAccessPanel(Panel $panel): bool
    {
        if (!($this->status ?? true)) {
            return false;
        }

        $panelId = $panel->getId();

        return match ($panelId) {
            'admin' => $this->hasRole(['superadmin', 'admin', 'administrator']),
            'pastor' => $this->hasRole(['pastor-paroki', 'pastor', 'superadmin', 'admin']),
            'sekretariat' => $this->hasRole(['sekretariat', 'tata-usaha', 'sekretaris', 'superadmin', 'admin', 'pastor-paroki']),
            'bendahara' => $this->hasRole(['bendahara', 'keuangan', 'kasir', 'superadmin', 'admin', 'pastor-paroki']),
            'umat' => true,
            default => true,
        };
    }

    public function getFilamentName(): string
    {
        return $this->nama_lengkap ?? $this->username ?? $this->email;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function kapela()
    {
        return $this->belongsTo(Kapela::class);
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
        if (!$this->role) {
            return true; // Default allow all if no role assigned
        }

        $currentSlug = strtolower(trim($this->role->slug ?? $this->role->nama_role ?? ''));

        // Superadmin and Pastor Paroki always have full access
        if (in_array($currentSlug, ['superadmin', 'admin', 'pastor-paroki', 'administrator', 'pastor'])) {
            return true;
        }

        if (is_array($roles)) {
            $normalized = array_map(fn ($r) => strtolower(trim($r)), $roles);
            return in_array($currentSlug, $normalized);
        }

        return $currentSlug === strtolower(trim($roles));
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
