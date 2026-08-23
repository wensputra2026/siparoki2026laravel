<?php

namespace App\Policies;

use App\Models\Konten;
use App\Models\User;

class KontenPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function view(User $user, Konten $konten): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function update(User $user, Konten $konten): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function delete(User $user, Konten $konten): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'komsos']);
    }
}
