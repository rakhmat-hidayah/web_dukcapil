<?php

namespace App\Services;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log a custom operator activity.
     */
    public static function log(string $description, $model = null, string $event = 'custom', array $properties = []): Activity
    {
        $logger = activity();

        if (Auth::check()) {
            $logger->causedBy(Auth::user());
        }

        if ($model) {
            $logger->performedOn($model);
        }

        if (!empty($properties)) {
            $logger->withProperties($properties);
        }

        return $logger->event($event)->log($description);
    }
}
