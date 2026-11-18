<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\AutoRoleSync;
use App\Services\ModulePermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    /**
     * Display a listing of users and roles.
     */
    public function index(Request $request): InertiaResponse
    {
        // Auto-synchronize roles if missing or unmigrated
        AutoRoleSync::sync();

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
                'role'               => $user->roles->first()?->name ?? 'Editor Konten',
                'roles'              => $user->roles->pluck('name')->toArray(),
                'last_login_at'      => $user->last_login_at?->format('d M Y H:i') ?? '—',
                'created_at'         => $user->created_at->format('d M Y'),
            ];
        });

        $protectedRoles = ModulePermissionService::getProtectedRoles();

        $roles = Role::with('permissions')->orderBy('name')->get()->map(function ($r) use ($protectedRoles) {
            return [
                'id'           => $r->id,
                'name'         => $r->name,
                'title'        => AutoRoleSync::resolveRoleName($r->name),
                'is_protected' => in_array($r->name, $protectedRoles, true),
                'users_count'  => $r->users()->count(),
                'permissions'  => $r->permissions->pluck('name')->toArray(),
            ];
        });

        $modules = ModulePermissionService::getModules();

        return Inertia::render('Admin/Users/Index', [
            'users'   => $users,
            'roles'   => $roles,
            'modules' => $modules,
            'search'  => $search,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        AutoRoleSync::sync();

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
            'role'     => 'nullable|string',
            'roles'    => 'nullable|array',
            'roles.*'  => 'string|exists:roles,name',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan.',
            'username.regex'    => 'Username hanya boleh mengandung huruf kecil, angka, dan underscore (4–30 karakter).',
            'email.unique'      => 'Alamat email sudah terdaftar.',
        ]);

        $user = User::create([
            'username'  => $request->username,
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        // Assign roles (supports array of roles or fallback single role)
        if ($request->has('roles') && is_array($request->roles) && !empty($request->roles)) {
            $user->syncRoles($request->roles);
        } else {
            $canonicalRole = AutoRoleSync::resolveRoleName($request->role ?? 'Editor Konten');
            Role::firstOrCreate(['name' => $canonicalRole, 'guard_name' => 'web']);
            $user->syncRoles([$canonicalRole]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLogger::log(
            "Membuat user baru: {$user->username} ({$user->name})",
            $user,
            'create_user',
            ['username' => $user->username, 'email' => $user->email, 'roles' => $user->roles->pluck('name')->toArray()]
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
            'role'      => 'nullable|string',
            'roles'     => 'nullable|array',
            'roles.*'   => 'string|exists:roles,name',
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

        // Assign roles (supports array of roles or fallback single role)
        if ($request->has('roles') && is_array($request->roles) && !empty($request->roles)) {
            $roles = $request->roles;
            if ($user->username === 'superadmin' && !in_array('Administrator Utama', $roles, true)) {
                $roles[] = 'Administrator Utama';
            }
            $user->syncRoles($roles);
        } elseif ($request->filled('role')) {
            $canonicalRole = AutoRoleSync::resolveRoleName($request->role);
            Role::firstOrCreate(['name' => $canonicalRole, 'guard_name' => 'web']);
            $user->syncRoles([$canonicalRole]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLogger::log(
            "Memperbarui user: {$user->username} ({$user->name})",
            $user,
            'update_user',
            ['username' => $user->username, 'email' => $user->email]
        );

        return redirect()->back()->with('success', "Operator '{$user->username}' berhasil diperbarui.");
    }

    /**
     * Dedicated action to change/add/remove roles for a specific user.
     */
    public function syncRoles(Request $request, User $user): RedirectResponse
    {
        if ($user->username === 'superadmin' && $request->user()->username !== 'superadmin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak untuk mengubah peran Super Admin Utama.');
        }

        $request->validate([
            'roles'   => 'required|array|min:1',
            'roles.*' => 'string|exists:roles,name',
        ], [
            'roles.required' => 'Pilih minimal satu peran untuk operator ini.',
            'roles.min'      => 'Pilih minimal satu peran untuk operator ini.',
        ]);

        $roles = $request->roles;
        if ($user->username === 'superadmin' && !in_array('Administrator Utama', $roles, true)) {
            $roles[] = 'Administrator Utama';
        }

        $user->syncRoles($roles);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLogger::log(
            "Memperbarui alokasi peran operator: {$user->username} menjadi [" . implode(', ', $roles) . "]",
            $user,
            'sync_user_roles',
            ['username' => $user->username, 'roles' => $roles]
        );

        return redirect()->back()->with('success', "Peran operator '{$user->username}' berhasil diperbarui.");
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
