<?php

namespace App\Policies;

use App\Models\Aset;
use App\Models\User;

class AsetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'bendahara']);
    }

    public function view(User $user, Aset $aset): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'bendahara']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'bendahara']);
    }

    public function update(User $user, Aset $aset): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'bendahara']);
    }

    public function delete(User $user, Aset $aset): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }
}
