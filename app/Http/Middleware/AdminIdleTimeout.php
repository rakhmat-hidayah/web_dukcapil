<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class AdminIdleTimeout
{
    /**
     * Maximum idle duration in seconds before admin session expires (15 minutes).
     */
    public const TIMEOUT_SECONDS = 900; // 15 minutes * 60 seconds

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('admin_last_activity_time');
            $currentTime = now()->timestamp;

            if ($lastActivity && ($currentTime - $lastActivity) > self::TIMEOUT_SECONDS) {
                $user = Auth::user();
                if ($user) {
                    ActivityLogger::log("Sesi admin otomatis berakhir karena tidak ada aktivitas selama 15 menit — {$user->username}", $user, 'session_timeout', [
                        'username' => $user->username,
                        'email'    => $user->email,
                        'idle_seconds' => $currentTime - $lastActivity,
                    ]);
                }

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = 'Sesi Anda telah berakhir otomatis karena tidak ada aktivitas selama 15 menit. Silakan login kembali.';

                if ($request->header('X-Inertia')) {
                    return Inertia::location(route('admin.login', ['timeout' => 1]));
                }

                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => $message,
                        'redirect' => route('admin.login', ['timeout' => 1]),
                    ], 401);
                }

                return redirect()->route('admin.login', ['timeout' => 1])
                    ->with('warning', $message);
            }

            // Keep timestamp updated on active request
            session(['admin_last_activity_time' => $currentTime]);
        }

        return $next($request);
    }
}
