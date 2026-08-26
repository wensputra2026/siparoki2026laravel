<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Response;

class UserController extends BaseAdminController
{
    /**
     * Display list of users.
     */
    public function index(Request $request): Response
    {
        $query = User::with(['role', 'paroki', 'wilayah', 'kapela', 'kub']);
        $this->applyTenantScope($query, $request);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $items = $query->orderBy('name')->paginate($request->input('per_page', 15))->withQueryString();
        $lookups = $this->getCommonLookups($request);

        return $this->renderInertia('Inertia/GenericModule', array_merge($lookups, [
            'title' => 'Manajemen Pengguna & Hak Akses',
            'moduleKey' => 'users',
            'items' => $items,
            'filters' => $request->only(['search', 'role_id', 'status']),
        ]), $request);
    }

    /**
     * Store new User.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'required|integer',
            'paroki_id' => 'nullable|integer',
            'wilayah_id' => 'nullable|integer',
            'kapela_id' => 'nullable|integer',
            'kub_id' => 'nullable|integer',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        if (empty($validated['paroki_id'])) {
            $validated['paroki_id'] = auth()->user()?->paroki_id ?? 1;
        }

        $user = User::create($validated);

        return redirect()->back()->with('success', "Pengguna {$user->name} ({$user->username}) berhasil didaftarkan!");
    }

    /**
     * Update User.
     */
    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'email' => 'required|email|max:100|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'role_id' => 'required|integer',
            'paroki_id' => 'nullable|integer',
            'wilayah_id' => 'nullable|integer',
            'kapela_id' => 'nullable|integer',
            'kub_id' => 'nullable|integer',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->back()->with('success', "Data Pengguna {$user->name} berhasil diperbarui!");
    }

    /**
     * Destroy User.
     */
    public function destroy(int $id)
    {
        if (auth()->id() === (int)$id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang aktif.');
        }

        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();

        return redirect()->back()->with('success', "Pengguna {$name} telah dihapus.");
    }
}
