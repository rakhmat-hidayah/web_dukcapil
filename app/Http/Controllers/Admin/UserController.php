<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): InertiaResponse
    {
        $search = $request->get('search', '');

        $query = User::with('roles')->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->get()->map(function ($user) {
            return [
                'id'                 => $user->id,
                'username'           => $user->username,
                'name'               => $user->name,
                'email'              => $user->email,
                'is_active'          => $user->is_active,
                'two_factor_enabled' => $user->hasTwoFactorEnabled(),
                'role'               => $user->roles->first()?->name ?? 'Content Editor',
                'roles'      => $user->roles->pluck('name'),
                'last_login_at' => $user->last_login_at?->format('d M Y H:i') ?? '—',
                'created_at' => $user->created_at->format('d M Y'),
            ];
        });

        $roles = Role::orderBy('name')->pluck('name');

        return Inertia::render('Admin/Users/Index', [
            'users'  => $users,
            'roles'  => $roles,
            'search' => $search,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => [
                'required',
                'string',
                'max:30',
                'min:4',
                'regex:/^[a-z0-9_]{4,30}$/',
                'unique:users,username',
            ],
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|string|exists:roles,name',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan.',
            'username.regex'    => 'Username hanya boleh mengandung huruf kecil, angka, dan underscore (4–30 karakter).',
            'email.unique'      => 'Alamat email sudah terdaftar.',
        ]);

        $user = User::create([
            'username' => $request->username,
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        $user->syncRoles([$request->role]);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLogger::log(
            "Membuat user baru: {$user->username} ({$user->name})",
            $user,
            'create_user',
            ['username' => $user->username, 'email' => $user->email, 'role' => $request->role]
        );

        return redirect()->back()->with('success', "Operator '{$user->username}' berhasil dibuat.");
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // Protect the primary Super Admin from being edited by non-superadmins
        if (
            $user->username === 'superadmin' &&
            $request->user()->username !== 'superadmin'
        ) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak untuk mengubah akun Super Admin Utama.');
        }

        $request->validate([
            'username' => [
                'required',
                'string',
                'max:30',
                'min:4',
                'regex:/^[a-z0-9_]{4,30}$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'name'     => 'required|string|max:255',
            'email'    => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password'  => 'nullable|string|min:8',
            'role'      => 'required|string|exists:roles,name',
            'is_active' => 'boolean',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan oleh user lain.',
            'username.regex'    => 'Username hanya boleh mengandung huruf kecil, angka, dan underscore (4–30 karakter).',
        ]);

        $user->username  = $request->username;
        $user->name      = $request->name;
        $user->email     = $request->email;
        $user->is_active = $request->boolean('is_active', true);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $user->syncRoles([$request->role]);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLogger::log(
            "Memperbarui user: {$user->username} ({$user->name})",
            $user,
            'update_user',
            ['username' => $user->username, 'email' => $user->email]
        );

        return redirect()->back()->with('success', "Operator '{$user->username}' berhasil diperbarui.");
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user, Request $request): RedirectResponse
    {
        // Cannot delete own account
        if ($user->id === $request->user()->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Cannot delete primary super admin
        if ($user->username === 'superadmin') {
            return redirect()->back()->with('error', 'Akun Super Admin Utama tidak dapat dihapus.');
        }

        ActivityLogger::log(
            "Menghapus user: {$user->username} ({$user->name})",
            $user,
            'delete_user',
            ['username' => $user->username, 'email' => $user->email]
        );

        $user->delete();

        return redirect()->back()->with('success', "User '{$user->username}' berhasil dihapus.");
    }

    /**
     * Toggle user active status (AJAX / quick action).
     */
    public function toggleActive(User $user, Request $request): RedirectResponse
    {
        if ($user->username === 'superadmin') {
            return redirect()->back()->with('error', 'Akun Super Admin Utama tidak dapat dinonaktifkan.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        ActivityLogger::log(
            "Akun user {$status}: {$user->username}",
            $user,
            'toggle_user_active',
            ['username' => $user->username, 'is_active' => $user->is_active]
        );

        return redirect()->back()->with('success', "Akun '{$user->username}' berhasil {$status}.");
    }
}
