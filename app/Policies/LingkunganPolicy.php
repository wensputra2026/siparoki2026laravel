<?php

namespace App\Policies;

use App\Models\Lingkungan;
use App\Models\User;

class LingkunganPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah', 'ketua-lingkungan']);
    }

    public function view(User $user, Lingkungan $lingkungan): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah', 'ketua-lingkungan']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah']);
    }

    public function update(User $user, Lingkungan $lingkungan): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah', 'ketua-lingkungan']);
    }

    public function delete(User $user, Lingkungan $lingkungan): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }
}
