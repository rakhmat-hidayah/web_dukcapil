<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
        \App\Console\Commands\PurgeExpiredDraftApplicationsCommand::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->redirectTo(
            guests: '/admin/login',
            users: '/admin/dashboard',
        );

        $middleware->validateCsrfTokens(except: [
            'layanan/antrian/daftar/*/dokumen',
            'layanan/antrian/daftar/*/submit',
            'layanan/antrian/status/*/booking',
            'layanan/antrian/status/*/resubmit',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\FixStoragePaths::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\TrackVisitors::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\EnsureTwoFactorAuthenticated::class,
        ]);

        $middleware->alias([
            'api.auth' => \App\Http\Middleware\AuthenticateApiKey::class,
            'api.log' => \App\Http\Middleware\LogApiRequests::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \App\Http\Middleware\SafeRoleOrPermissionMiddleware::class,
            '2fa' => \App\Http\Middleware\EnsureTwoFactorAuthenticated::class,
            'record.visitor' => \App\Http\Middleware\RecordVisitor::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson() || $request->is('layanan/antrian/daftar/*/dokumen'),
        );
    })->create();
