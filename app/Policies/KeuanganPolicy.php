<?php

namespace App\Policies;

use App\Models\Keuangan;
use App\Models\User;

class KeuanganPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'bendahara']);
    }

    public function view(User $user, Keuangan $keuangan): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'bendahara']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'bendahara']);
    }

    public function update(User $user, Keuangan $keuangan): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'bendahara']);
    }

    public function delete(User $user, Keuangan $keuangan): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }
}
