<?php

namespace App\Http\Controllers;

use App\Models\ChatPesan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Get available contacts list with last message and unread count
     */
    public function getContacts(Request $request): JsonResponse
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) {
            return response()->json(['contacts' => [], 'total_unread' => 0], 401);
        }

        $search = trim((string) $request->query('search', ''));

        $usersQuery = User::with(['role', 'wilayah', 'kapela', 'kub'])
            ->where('id', '!=', $currentUserId)
            ->where(function ($q) {
                $q->where('status', 1)->orWhere('status', 'Aktif')->orWhereNull('status');
            });

        if ($search !== '') {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('kub', fn($qq) => $qq->where('nama_kub', 'like', "%{$search}%"))
                  ->orWhereHas('wilayah', fn($qq) => $qq->where('nama_wilayah', 'like', "%{$search}%"))
                  ->orWhereHas('kapela', fn($qq) => $qq->where('nama_kapela', 'like', "%{$search}%"))
                  ->orWhereHas('role', fn($qq) => $qq->where('nama_role', 'like', "%{$search}%"));
            });
        }

        $users = $usersQuery->get();

        // Get latest message for each conversation
        $contacts = $users->map(function ($user) use ($currentUserId) {
            $lastMsg = ChatPesan::betweenUsers($currentUserId, $user->id)
                ->latest('created_at')
                ->first();

            $unreadCount = ChatPesan::where('pengirim_id', $user->id)
                ->where('penerima_id', $currentUserId)
                ->where('is_read', false)
                ->count();

            // Determine role/territory label
            $roleLabel = $user->role?->nama_role ?: $user->role?->name ?: 'Pengguna';
            if ($user->kub) {
                $roleLabel = "KUB " . $user->kub->nama_kub;
            } elseif ($user->kapela) {
                $roleLabel = "Stasi/Kapela " . $user->kapela->nama_kapela;
            } elseif ($user->wilayah) {
                $roleLabel = "Wilayah " . $user->wilayah->nama_wilayah;
            }

            return [
                'id' => $user->id,
                'name' => $user->nama_lengkap ?: ($user->username ?: 'Pengguna Paroki'),
                'username' => $user->username,
                'foto' => $user->foto,
                'role_name' => $roleLabel,
                'last_message' => $lastMsg ? $lastMsg->pesan : null,
                'last_message_time' => $lastMsg ? $lastMsg->created_at->toIso8601String() : null,
                'unread_count' => $unreadCount,
            ];
        });

        // Sort: contacts with latest messages first, then by name
        $sortedContacts = $contacts->sortByDesc(function ($contact) {
            return $contact['last_message_time'] ?? '1970-01-01T00:00:00Z';
        })->values();

        $totalUnread = ChatPesan::where('penerima_id', $currentUserId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'contacts' => $sortedContacts,
            'total_unread' => $totalUnread,
        ]);
    }

    /**
     * Get chat conversation history with specific user
     */
    public function getMessages($recipientId): JsonResponse
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) {
            return response()->json(['messages' => []], 401);
        }

        $recipient = User::with(['role', 'wilayah', 'kapela', 'kub'])->find($recipientId);
        if (!$recipient) {
            return response()->json(['error' => 'Kontak tidak ditemukan.'], 404);
        }

        // Mark incoming messages as read
        ChatPesan::where('pengirim_id', $recipientId)
            ->where('penerima_id', $currentUserId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $messages = ChatPesan::betweenUsers($currentUserId, $recipientId)
            ->orderBy('created_at', 'asc')
            ->limit(150)
            ->get()
            ->map(function ($msg) use ($currentUserId) {
                return [
                    'id' => $msg->id,
                    'pengirim_id' => $msg->pengirim_id,
                    'penerima_id' => $msg->penerima_id,
                    'is_me' => $msg->pengirim_id == $currentUserId,
                    'pesan' => $msg->pesan,
                    'lampiran' => $msg->lampiran,
                    'is_read' => $msg->is_read,
                    'created_at' => $msg->created_at->toIso8601String(),
                    'time_formatted' => $msg->created_at->format('H:i'),
                ];
            });

        $roleLabel = $recipient->role?->nama_role ?: 'Pengguna';
        if ($recipient->kub) {
            $roleLabel = "KUB " . $recipient->kub->nama_kub;
        } elseif ($recipient->kapela) {
            $roleLabel = "Stasi/Kapela " . $recipient->kapela->nama_kapela;
        } elseif ($recipient->wilayah) {
            $roleLabel = "Wilayah " . $recipient->wilayah->nama_wilayah;
        }

        return response()->json([
            'recipient' => [
                'id' => $recipient->id,
                'name' => $recipient->nama_lengkap ?: ($recipient->username ?: 'Pengguna Paroki'),
                'username' => $recipient->username,
                'foto' => $recipient->foto,
                'role_name' => $roleLabel,
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Send a new chat message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'penerima_id' => 'required|integer|exists:users,id',
            'pesan' => 'required|string|max:3000',
            'lampiran' => 'nullable|string|max:500',
        ]);

        $created = ChatPesan::create([
            'pengirim_id' => $currentUserId,
            'penerima_id' => $validated['penerima_id'],
            'pesan' => trim($validated['pesan']),
            'lampiran' => $validated['lampiran'] ?? null,
            'is_read' => false,
        ]);

        $sender = Auth::user();

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $created->id,
                'pengirim_id' => $created->pengirim_id,
                'penerima_id' => $created->penerima_id,
                'is_me' => true,
                'pesan' => $created->pesan,
                'lampiran' => $created->lampiran,
                'is_read' => false,
                'created_at' => $created->created_at->toIso8601String(),
                'time_formatted' => $created->created_at->format('H:i'),
                'sender_name' => $sender->nama_lengkap ?: $sender->username,
            ],
        ]);
    }

    /**
     * Poll incoming new messages for real-time update
     */
    public function poll(Request $request): JsonResponse
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) {
            return response()->json(['new_messages' => [], 'total_unread' => 0]);
        }

        $lastId = (int) $request->query('last_id', 0);

        $newMessages = ChatPesan::with(['pengirim'])
            ->where('penerima_id', $currentUserId)
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'pengirim_id' => $msg->pengirim_id,
                    'penerima_id' => $msg->penerima_id,
                    'pesan' => $msg->pesan,
                    'pengirim_name' => $msg->pengirim?->nama_lengkap ?: ($msg->pengirim?->username ?: 'Pengguna'),
                    'pengirim_foto' => $msg->pengirim?->foto,
                    'created_at' => $msg->created_at->toIso8601String(),
                    'time_formatted' => $msg->created_at->format('H:i'),
                ];
            });

        $totalUnread = ChatPesan::where('penerima_id', $currentUserId)
            ->where('is_read', false)
            ->count();

        $latestId = ChatPesan::where('penerima_id', $currentUserId)->max('id') ?: $lastId;

        return response()->json([
            'new_messages' => $newMessages,
            'total_unread' => $totalUnread,
            'latest_id' => $latestId,
        ]);
    }

    /**
     * Mark all messages from specific sender as read
     */
    public function markRead($senderId): JsonResponse
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) {
            return response()->json(['success' => false]);
        }

        ChatPesan::where('pengirim_id', $senderId)
            ->where('penerima_id', $currentUserId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $totalUnread = ChatPesan::where('penerima_id', $currentUserId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'total_unread' => $totalUnread,
        ]);
    }
}
