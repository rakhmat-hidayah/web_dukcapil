<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // If user's role requires 2FA by policy but user has not set it up yet,
        // force redirect to Security setup page (unless already on security page or logout)
        if (
            $user->requiresTwoFactorByRole() &&
            !$user->hasTwoFactorEnabled() &&
            !$request->routeIs('admin.security.*') &&
            !$request->routeIs('admin.logout')
        ) {
            return redirect()->route('admin.security.index')->with(
                'warning',
                'Peran akun Anda mewajibkan penggunaan Autentikasi Dua Faktor (2FA). Harap aktifkan 2FA terlebih dahulu untuk melanjutkan.'
            );
        }

        return $next($request);
    }
}
