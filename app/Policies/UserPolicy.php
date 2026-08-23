<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']) || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']) || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']) && $user->id !== $model->id;
    }
}
