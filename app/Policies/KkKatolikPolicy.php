<?php

namespace App\Policies;

use App\Models\KkKatolik;
use App\Models\User;

class KkKatolikPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah', 'ketua-lingkungan']);
    }

    public function view(User $user, KkKatolik $kk): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah', 'ketua-lingkungan']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah', 'ketua-lingkungan']);
    }

    public function update(User $user, KkKatolik $kk): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah', 'ketua-lingkungan']);
    }

    public function delete(User $user, KkKatolik $kk): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }
}
