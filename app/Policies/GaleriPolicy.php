<?php

namespace App\Policies;

use App\Models\Galeri;
use App\Models\User;

class GaleriPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function view(User $user, Galeri $galeri): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function update(User $user, Galeri $galeri): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'komsos']);
    }

    public function delete(User $user, Galeri $galeri): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'komsos']);
    }
}
