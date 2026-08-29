<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Get list of notifications for the active user / role.
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unread_count' => 0, 'notifications' => []]);
        }

        $query = $this->getScopedQuery($user);

        $unreadCount = (clone $query)->where('is_read', false)->count();
        $notifications = $query->latest('id')->take(20)->get();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Poll for new notifications since last_id (Real-time polling fallback).
     */
    public function poll(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['new_items' => [], 'unread_count' => 0]);
        }

        $lastId = (int) $request->query('last_id', 0);
        $query = $this->getScopedQuery($user);

        $newItems = [];
        if ($lastId > 0) {
            $newItems = (clone $query)->where('id', '>', $lastId)->latest('id')->get();
        }

        $unreadCount = (clone $query)->where('is_read', false)->count();

        return response()->json([
            'new_items' => $newItems,
            'unread_count' => $unreadCount,
            'latest_id' => Notifikasi::max('id') ?? 0,
        ]);
    }

    /**
     * Mark a single notification or all as read.
     */
    public function markAsRead(Request $request, $id = null)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        if ($id && $id !== 'all') {
            Notifikasi::where('id', $id)->update(['is_read' => true]);
        } else {
            $query = $this->getScopedQuery($user);
            $query->where('is_read', false)->update(['is_read' => true]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Scope query based on user's role and assigned territory (KUB, Wilayah, Kapela).
     */
    private function getScopedQuery($user)
    {
        $roleSlug = strtolower(preg_replace('/[^a-z0-9]/', '', $user->role?->slug ?? $user->role?->nama_role ?? ''));
        $isSuperAdmin = ($user->role_id === 1 || $user->id === 1 || in_array($roleSlug, ['superadmin', 'superadministrator', 'adminparoki', 'pastor', 'sekretariat'], true));

        $query = Notifikasi::query();

        if ($isSuperAdmin) {
            // Elevated admins see all notifications
            return $query;
        }

        return $query->where(function ($q) use ($user, $roleSlug) {
            $q->where('user_id', $user->id)
              ->orWhere('role_target', 'all');

            if ($user->kub_id || str_contains($roleSlug, 'kub')) {
                $q->orWhere('kub_id', $user->kub_id);
            }
            if ($user->wilayah_id || str_contains($roleSlug, 'wilayah')) {
                $q->orWhere('wilayah_id', $user->wilayah_id);
            }
            if ($user->kapela_id || str_contains($roleSlug, 'kapela') || str_contains($roleSlug, 'stasi')) {
                $q->orWhere('kapela_id', $user->kapela_id);
            }
        });
    }
}
