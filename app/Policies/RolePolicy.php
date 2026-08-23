<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }
}
