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
 * Purge Expired Online Registration Drafts (> 1x24 Jam)
 * Runs hourly to clean up abandoned drafts, delete physical files, and expire user sessions
 */
Schedule::command('queue:purge-expired-drafts')
    ->hourly()
    ->withoutOverlapping(15);
