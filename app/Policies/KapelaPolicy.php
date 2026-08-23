<?php

namespace App\Policies;

use App\Models\Kapela;
use App\Models\User;

class KapelaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah']);
    }

    public function view(User $user, Kapela $kapela): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }

    public function update(User $user, Kapela $kapela): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }

    public function delete(User $user, Kapela $kapela): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }
}
