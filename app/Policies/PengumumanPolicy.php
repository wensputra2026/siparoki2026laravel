<?php

namespace App\Policies;

use App\Models\Pengumuman;
use App\Models\User;

class PengumumanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function view(User $user, Pengumuman $pengumuman): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function update(User $user, Pengumuman $pengumuman): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function delete(User $user, Pengumuman $pengumuman): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'komsos']);
    }
}
