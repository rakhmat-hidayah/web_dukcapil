<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Visitor Analytics Automatic Cleanup Scheduler
 * Runs daily at 02:00 WIB (overlap protected)
 */
Schedule::command('visitor:cleanup', ['--triggered-by' => 'scheduler'])
    ->dailyAt('02:00')
    ->withoutOverlapping(30);

/**
 * Purge Expired Online Registration Drafts
 * Runs hourly to clean up abandoned drafts, delete physical files, and expire user sessions
 */
Schedule::command(\App\Console\Commands\PurgeExpiredDraftApplicationsCommand::class)
    ->hourly()
    ->withoutOverlapping(15);

/**
 * Archive Retention & Cleaning Grace Period Automatic Cleanup
 * Runs daily at 02:30 WIB (overlap protected)
 */
Schedule::command(\App\Console\Commands\ArchiveRetentionCleanupCommand::class, ['--triggered-by' => 'scheduler'])
    ->dailyAt('02:30')
    ->withoutOverlapping(30);

/**
 * Temporary Selfie Verification Cleanup & Retry
 * Runs every thirty minutes to ensure any pending or failed selfie physical deletions are completely purged
 */
Schedule::command(\App\Console\Commands\RetrySelfieDeletionsCommand::class)
    ->everyThirtyMinutes()
    ->withoutOverlapping(10);
