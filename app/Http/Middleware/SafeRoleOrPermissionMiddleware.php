<?php

namespace App\Http\Middleware;

use App\Services\AutoRoleSync;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class SafeRoleOrPermissionMiddleware
{
    /**
     * Safely authorize user by role or permission without throwing PermissionDoesNotExist or RoleDoesNotExist.
     */
    public function handle(Request $request, Closure $next, $roleOrPermission, ?string $guard = null)
    {
        $authGuard = Auth::guard($guard);
        $user = $authGuard->user();

        if (!$user) {
            throw UnauthorizedException::notLoggedIn();
        }

        // Super Administrator / Administrator Utama has full bypass
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $next($request);
        }

        $rolesOrPermissions = is_array($roleOrPermission)
            ? $roleOrPermission
            : explode('|', (string) $roleOrPermission);

        // 1. Check user roles safely in memory (supporting legacy and Indonesian canonical names)
        $userRoleNames = $user->roles->pluck('name')->toArray();
        foreach ($rolesOrPermissions as $item) {
            $trimmed = trim($item);
            $canonical = AutoRoleSync::resolveRoleName($trimmed);
            if (in_array($trimmed, $userRoleNames, true) || in_array($canonical, $userRoleNames, true)) {
                return $next($request);
            }
        }

        // 2. Check user permissions safely in memory
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        foreach ($rolesOrPermissions as $item) {
            $trimmed = trim($item);
            if (in_array($trimmed, $userPermissions, true)) {
                return $next($request);
            }
        }

        throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
    }
}
