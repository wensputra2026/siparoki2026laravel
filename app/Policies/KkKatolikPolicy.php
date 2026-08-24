<?php

namespace App\Policies;

use App\Models\KkKatolik;
use App\Models\User;

class KkKatolikPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah', 'ketua-lingkungan', 'ketua-kub']);
    }

    public function view(User $user, KkKatolik $kk): bool
    {
        if ($user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat'])) {
            return true;
        }

        if ($user->hasRole('ketua-wilayah') && $user->wilayah_id) {
            return (int)$user->wilayah_id === (int)$kk->wilayah_id;
        }

        if ($user->hasRole(['ketua-lingkungan', 'ketua-kub'])) {
            if ($user->kub_id && (int)$user->kub_id === (int)($kk->kub_id ?? 0)) {
                return true;
            }
            if ($user->lingkungan_id && (int)$user->lingkungan_id === (int)($kk->lingkungan_id ?? 0)) {
                return true;
            }
        }

        // Self / Family scope
        if ($user->umat && $user->umat->kk_id) {
            return (int)$user->umat->kk_id === (int)$kk->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat', 'ketua-wilayah', 'ketua-lingkungan', 'ketua-kub']);
    }

    public function update(User $user, KkKatolik $kk): bool
    {
        if ($user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat'])) {
            return true;
        }

        if ($user->hasRole('ketua-wilayah') && $user->wilayah_id) {
            return (int)$user->wilayah_id === (int)$kk->wilayah_id;
        }

        if ($user->hasRole(['ketua-lingkungan', 'ketua-kub'])) {
            if ($user->kub_id && (int)$user->kub_id === (int)($kk->kub_id ?? 0)) {
                return true;
            }
            if ($user->lingkungan_id && (int)$user->lingkungan_id === (int)($kk->lingkungan_id ?? 0)) {
                return true;
            }
        }

        if ($user->umat && $user->umat->kk_id) {
            return (int)$user->umat->kk_id === (int)$kk->id;
        }

        return false;
    }

    public function delete(User $user, KkKatolik $kk): bool
    {
        return $user->hasRole(['superadmin', 'admin', 'pastor-paroki', 'sekretariat']);
    }
}
