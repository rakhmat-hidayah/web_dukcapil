<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;

abstract class Controller
{
    /**
     * Authorize a given action for the current user.
     */
    public function authorize(mixed $ability, mixed $arguments = []): mixed
    {
        try {
            return Gate::authorize($ability, $arguments);
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            try {
                (new \Database\Seeders\KtpBlankStockPermissionSeeder())->run();
                return Gate::authorize($ability, $arguments);
            } catch (\Throwable $inner) {
                abort(403, 'Aksi ini tidak diizinkan untuk akun Anda.');
            }
        }
    }
}
