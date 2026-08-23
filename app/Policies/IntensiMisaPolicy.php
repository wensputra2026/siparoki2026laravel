<?php

namespace App\Policies;

use App\Models\IntensiMisa;
use App\Models\User;

class IntensiMisaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'bendahara']);
    }

    public function view(User $user, IntensiMisa $intensi): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'bendahara']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'bendahara']);
    }

    public function update(User $user, IntensiMisa $intensi): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'bendahara']);
    }

    public function delete(User $user, IntensiMisa $intensi): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }
}
