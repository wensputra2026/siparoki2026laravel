<?php

namespace App\Policies;

use App\Models\Sakramen;
use App\Models\User;

class SakramenPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }

    public function view(User $user, Sakramen $sakramen): bool
    {
        if ($user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat'])) {
            return true;
        }

        return $user->umat_id && (int)$user->umat_id === (int)$sakramen->id_umat;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }

    public function update(User $user, Sakramen $sakramen): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }

    public function delete(User $user, Sakramen $sakramen): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }
}
