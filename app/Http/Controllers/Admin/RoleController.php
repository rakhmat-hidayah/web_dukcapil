<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\ModulePermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /**
     * Store a newly created role with associated permissions.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'          => 'required|string|max:100|unique:roles,name',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ], [
            'name.required' => 'Nama peran wajib diisi.',
            'name.unique'   => 'Nama peran sudah digunakan dalam sistem.',
        ]);

        $role = Role::create([
            'name'       => trim($request->name),
            'guard_name' => 'web',
        ]);

        if (!empty($request->permissions)) {
            $role->syncPermissions($request->permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLogger::log(
            "Membuat peran baru: {$role->name} dengan " . count($request->permissions ?? []) . " hak akses modul",
            $role,
            'create_role',
            ['role' => $role->name, 'permissions_count' => count($request->permissions ?? [])]
        );

        return redirect()->back()->with('success', "Peran '{$role->name}' berhasil ditambahkan.");
    }

    /**
     * Update the specified role and synchronize its permissions.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $protectedRoles = ModulePermissionService::getProtectedRoles();
        $isProtected = in_array($role->name, $protectedRoles, true);

        $request->validate([
            'name'          => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ], [
            'name.required' => 'Nama peran wajib diisi.',
            'name.unique'   => 'Nama peran sudah digunakan dalam sistem.',
        ]);

        // If not a protected system role name, allow changing the display name
        if (!$isProtected) {
            $role->name = trim($request->name);
            $role->save();
        }

        $permissions = $request->permissions ?? [];

        // Protection for Administrator Utama: cannot be completely stripped of essential administrative rights
        if ($role->name === 'Administrator Utama' || $role->name === 'Super Administrator') {
            $essential = ['manage_roles', 'manage_users', 'manage_settings', 'view_audit_logs'];
            $permissions = array_values(array_unique(array_merge($permissions, $essential)));
        }

        $role->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLogger::log(
            "Memperbarui hak akses peran: {$role->name} (" . count($permissions) . " hak akses modul)",
            $role,
            'update_role',
            ['role' => $role->name, 'permissions_count' => count($permissions)]
        );

        return redirect()->back()->with('success', "Peran '{$role->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $protectedRoles = ModulePermissionService::getProtectedRoles();

        if (in_array($role->name, $protectedRoles, true)) {
            return redirect()->back()->with('error', "Peran bawaan sistem '{$role->name}' tidak dapat dihapus demi integritas modul website.");
        }

        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            return redirect()->back()->with('error', "Peran '{$role->name}' tidak dapat dihapus karena masih digunakan oleh {$usersCount} operator.");
        }

        $roleName = $role->name;
        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLogger::log(
            "Menghapus peran: {$roleName}",
            null,
            'delete_role',
            ['role' => $roleName]
        );

        return redirect()->back()->with('success', "Peran '{$roleName}' berhasil dihapus.");
    }
}
