<?php

namespace App\Policies;

use App\Models\Paroki;
use App\Models\User;

class ParokiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah', 'ketua-lingkungan', 'bendahara', 'komsos']);
    }

    public function view(User $user, Paroki $paroki): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah', 'ketua-lingkungan', 'bendahara', 'komsos']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }

    public function update(User $user, Paroki $paroki): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }

    public function delete(User $user, Paroki $paroki): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }
}
