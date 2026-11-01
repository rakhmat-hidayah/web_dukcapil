<?php

namespace App\Http\Controllers\Public\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\KtpHandover;
use App\Models\Queue\QueueApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueTrackController extends Controller
{
    /**
     * Universal QR Code & Barcode Tracker Resolver.
     * Accessible by both citizens (redirects to live status) and officers (redirects to admin workspace).
     */
    public function track(string $code): RedirectResponse
    {
        $code = trim($code);

        // Sanitize code if scanned as a URL or prefixed string
        if (preg_match('/(REG-[A-Za-z0-9-]+)/i', $code, $matches)) {
            $code = strtoupper($matches[1]);
        }

        $application = QueueApplication::where('reg_number', $code)
            ->orWhere('access_token', $code)
            ->first();

        if (!$application) {
            return redirect()->route('queue.index')
                ->with('error', "Dokumen permohonan dengan kode \"{$code}\" tidak ditemukan di sistem.");
        }

        // If the scanner/device is operated by an authenticated officer/admin:
        if (Auth::check()) {
            $handover = KtpHandover::where('queue_application_id', $application->id)->first();
            if ($handover) {
                return redirect()->route('admin.handover.show', $handover->id)
                    ->with('success', "Dokumen KTP-el berhasil ditemukan melalui pemindaian QR Code.");
            }

            return redirect()->route('admin.queue.applications.show', $application->id)
                ->with('info', "Dokumen pendaftaran online ditemukan ({$application->reg_number}).");
        }

        // Public citizen tracking: direct to live dashboard
        return redirect()->route('queue.status', ['token' => $application->access_token]);
    }
}
