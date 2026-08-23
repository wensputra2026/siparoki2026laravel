<?php

namespace App\Policies;

use App\Models\Umat;
use App\Models\User;

class UmatPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah', 'ketua-lingkungan', 'ketua-kub']);
    }

    public function view(User $user, Umat $umat): bool
    {
        // Full access
        if ($user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat'])) {
            return true;
        }

        // Ketua Wilayah scope
        if ($user->hasRole('ketua-wilayah') && $user->wilayah_id) {
            return (int)$user->wilayah_id === (int)($umat->kk->wilayah_id ?? 0);
        }

        // Ketua Lingkungan / KUB scope
        if ($user->hasRole(['ketua-lingkungan', 'ketua-kub'])) {
            if ($user->kub_id && $umat->kk) {
                return (int)$user->kub_id === (int)($umat->kk->kub_id ?? 0);
            }
            if ($user->lingkungan_id && $umat->kk) {
                return (int)$user->lingkungan_id === (int)($umat->kk->lingkungan_id ?? 0);
            }
        }

        // Portal Umat / Self scope
        return $user->umat_id && (int)$user->umat_id === (int)$umat->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah', 'ketua-lingkungan', 'ketua-kub']);
    }

    public function update(User $user, Umat $umat): bool
    {
        if ($user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat'])) {
            return true;
        }

        if ($user->hasRole('ketua-wilayah') && $user->wilayah_id) {
            return (int)$user->wilayah_id === (int)($umat->kk->wilayah_id ?? 0);
        }

        if ($user->hasRole(['ketua-lingkungan', 'ketua-kub'])) {
            if ($user->kub_id && $umat->kk) {
                return (int)$user->kub_id === (int)($umat->kk->kub_id ?? 0);
            }
            if ($user->lingkungan_id && $umat->kk) {
                return (int)$user->lingkungan_id === (int)($umat->kk->lingkungan_id ?? 0);
            }
        }

        return $user->umat_id && (int)$user->umat_id === (int)$umat->id;
    }

    public function delete(User $user, Umat $umat): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }
}
