<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Models\Innovation;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MobileServiceController extends Controller
{
    /**
     * Render the public Mobile Service Schedule page.
     */
    public function index(): InertiaResponse
    {
        $settings = WebsiteSetting::where('group', 'informasi_layanan')->pluck('value', 'key')->toArray();

        // Get active innovations
        $innovations = Innovation::where('is_active', true)->orderBy('sort_order')->limit(6)->get();

        return Inertia::render('Public/MobileServices/Index', [
            'scheduleSettings' => $settings,
            'innovations' => $innovations,
        ]);
    }
}
