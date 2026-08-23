<?php

namespace App\Policies;

use App\Models\JadwalMisa;
use App\Models\User;

class JadwalMisaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function view(User $user, JadwalMisa $jadwal): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function update(User $user, JadwalMisa $jadwal): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function delete(User $user, JadwalMisa $jadwal): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }
}
