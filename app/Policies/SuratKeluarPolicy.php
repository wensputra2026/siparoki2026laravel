<?php

namespace App\Policies;

use App\Models\SuratKeluar;
use App\Models\User;

class SuratKeluarPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }

    public function view(User $user, SuratKeluar $surat): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }

    public function update(User $user, SuratKeluar $surat): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }

    public function delete(User $user, SuratKeluar $surat): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki']);
    }
}
