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
     * Check if internal chat feature is enabled globally
     */
    protected function isChatEnabled(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        $roleName = strtolower($user->role?->nama_role ?? '');
        if ((int) $user->role_id === 1 || str_contains($roleName, 'super')) {
            return true;
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('pengaturan_aplikasi')) {
            $val = \Illuminate\Support\Facades\DB::table('pengaturan_aplikasi')->value('fitur_chat_aktif');
            if ($val !== null && ((string) $val === '0' || $val === false)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get available contacts list with last message and unread count
     */
    public function getContacts(Request $request): JsonResponse
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) {
            return response()->json(['contacts' => [], 'total_unread' => 0], 401);
        }

        if (!$this->isChatEnabled()) {
            return response()->json(['contacts' => [], 'total_unread' => 0, 'disabled' => true]);
        }

        // Touch current user online cache
        \Illuminate\Support\Facades\Cache::put('user_online_' . $currentUserId, now()->timestamp, now()->addMinutes(3));

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

        // Get latest message and online status for each contact
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

            // Accurate online check: active in cache within last 3 minutes
            $isOnline = \Illuminate\Support\Facades\Cache::has('user_online_' . $user->id);

            return [
                'id' => $user->id,
                'name' => $user->nama_lengkap ?: ($user->username ?: 'Pengguna Paroki'),
                'username' => $user->username,
                'foto' => $user->foto,
                'role_name' => $roleLabel,
                'is_online' => $isOnline,
                'last_message' => $lastMsg ? $lastMsg->pesan : null,
                'last_message_time' => $lastMsg ? $lastMsg->created_at->toIso8601String() : null,
                'unread_count' => $unreadCount,
            ];
        });

        // Sort: online and unread first, then by last message time, then name
        $sortedContacts = $contacts->sortByDesc(function ($contact) {
            return ($contact['unread_count'] > 0 ? 2000000000 : 0) +
                   ($contact['is_online'] ? 1000000000 : 0) +
                   strtotime($contact['last_message_time'] ?? '1970-01-01');
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

        \Illuminate\Support\Facades\Cache::put('user_online_' . $currentUserId, now()->timestamp, now()->addMinutes(3));

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

        $isOnline = \Illuminate\Support\Facades\Cache::has('user_online_' . $recipient->id);

        return response()->json([
            'recipient' => [
                'id' => $recipient->id,
                'name' => $recipient->nama_lengkap ?: ($recipient->username ?: 'Pengguna Paroki'),
                'username' => $recipient->username,
                'foto' => $recipient->foto,
                'role_name' => $roleLabel,
                'is_online' => $isOnline,
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Send a new chat message with optional image/screenshot attachment
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $currentUserId = Auth::id();
        if (!$currentUserId) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if (!$this->isChatEnabled()) {
            return response()->json(['error' => 'Fitur chat internal sedang dinonaktifkan oleh administrator paroki.'], 403);
        }

        $validated = $request->validate([
            'penerima_id' => 'required|integer|exists:users,id',
            'pesan' => 'nullable|string|max:3000',
            'lampiran_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,gif,pdf|max:10240',
            'lampiran_base64' => 'nullable|string',
        ]);

        if (empty(trim((string) ($validated['pesan'] ?? ''))) && !$request->hasFile('lampiran_file') && empty($validated['lampiran_base64'])) {
            return response()->json(['error' => 'Pesan atau gambar tidak boleh kosong.'], 422);
        }

        $lampiranPath = null;
        $chatUploadDir = public_path('uploads/chat');
        if (!is_dir($chatUploadDir)) {
            @mkdir($chatUploadDir, 0755, true);
        }

        // 1. Handle File Upload
        if ($request->hasFile('lampiran_file')) {
            $file = $request->file('lampiran_file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($chatUploadDir, $fileName);
            $lampiranPath = 'uploads/chat/' . $fileName;
        }
        // 2. Handle Clipboard Screenshot Base64 Paste
        elseif (!empty($validated['lampiran_base64']) && str_starts_with($validated['lampiran_base64'], 'data:image/')) {
            $base64Data = $validated['lampiran_base64'];
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $ext = strtolower($type[1]);
                if (in_array($ext, ['jpeg', 'jpg', 'png', 'webp', 'gif'])) {
                    $decoded = base64_decode($base64Data);
                    if ($decoded !== false) {
                        $fileName = 'screenshot_' . time() . '_' . uniqid() . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                        file_put_contents($chatUploadDir . '/' . $fileName, $decoded);
                        $lampiranPath = 'uploads/chat/' . $fileName;
                    }
                }
            }
        }

        $created = ChatPesan::create([
            'pengirim_id' => $currentUserId,
            'penerima_id' => $validated['penerima_id'],
            'pesan' => trim((string) ($validated['pesan'] ?? '')),
            'lampiran' => $lampiranPath,
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
