<?php

namespace App\Services\Archive;

use App\Models\Archive\ArchiveAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ArchiveAuditService
{
    /**
     * Record an archive audit trail entry.
     */
    public static function log(
        string $action,
        ?int $packageId = null,
        ?int $applicationId = null,
        array $details = [],
        ?User $user = null
    ): ArchiveAuditLog {
        $actor = $user ?? Auth::user();

        return ArchiveAuditLog::create([
            'archive_package_id' => $packageId,
            'application_id' => $applicationId,
            'user_id' => $actor?->id,
            'action' => $action,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent(),
            'details' => $details,
            'created_at' => now(),
        ]);
    }
}
