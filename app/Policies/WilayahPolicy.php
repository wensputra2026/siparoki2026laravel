<?php

namespace App\Policies;

use App\Models\Wilayah;
use App\Models\User;

class WilayahPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah']);
    }

    public function view(User $user, Wilayah $wilayah): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }

    public function update(User $user, Wilayah $wilayah): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah']);
    }

    public function delete(User $user, Wilayah $wilayah): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }
}
