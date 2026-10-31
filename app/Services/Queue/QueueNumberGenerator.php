<?php

namespace App\Services\Queue;

use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSlot;
use Illuminate\Support\Str;

class QueueNumberGenerator
{
    /**
     * Generate temporary registration number for initial submission.
     * Format: REG-YYMM-RANDOM6 (e.g. REG-2610-8K2A9X)
     */
    public static function generateRegNumber(): string
    {
        $prefix = 'REG-' . date('ym') . '-';
        $attempts = 0;

        do {
            $number = $prefix . strtoupper(Str::random(6));
            $exists = QueueApplication::withTrashed()->where('reg_number', $number)->exists();
            $attempts++;
        } while ($exists && $attempts < 15);

        if ($attempts >= 15) {
            $number = $prefix . strtoupper(substr(uniqid(), -6));
        }

        return $number;
    }

    /**
     * Generate final queue number when application is verified and approved.
     * Format: {LETTER}-{001} based on service type prefix and sequence on that schedule.
     * Examples:
     * - H-001 (Hilang)
     * - R-001 (Rusak)
     * - P-001 (Perubahan)
     * - B-001 (Baru / Rekam)
     */
    public static function generateQueueNumber(QueueSlot $slot): string
    {
        $serviceType = $slot->serviceType;
        $slug = $serviceType ? $serviceType->slug : '';

        $prefixLetter = match (true) {
            str_contains($slug, 'hilang') => 'H',
            str_contains($slug, 'rusak') => 'R',
            str_contains($slug, 'perubahan') => 'P',
            str_contains($slug, 'rekam') => 'B',
            default => strtoupper(substr($slug, 0, 1)),
        };

        // Count approved/scheduled applications in this specific slot
        $currentCount = QueueApplication::where('queue_slot_id', $slot->id)
            ->whereNotNull('queue_number')
            ->count();

        $sequence = $currentCount + 1;

        return sprintf('%s-%03d', $prefixLetter, $sequence);
    }
}
