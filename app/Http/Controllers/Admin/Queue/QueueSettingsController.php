<?php

namespace App\Http\Controllers\Admin\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\QueueSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QueueSettingsController extends Controller
{
    public function index(): Response
    {
        $settings = QueueSetting::all()->groupBy('group')->map(function ($items) {
            return $items->keyBy('key');
        });

        $rejectionTemplates = \App\Models\Queue\QueueRejectionTemplate::orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return Inertia::render('Admin/Queue/Settings/Index', [
            'settings'           => $settings,
            'rejectionTemplates' => $rejectionTemplates,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->input('settings');
        if (!is_array($data)) {
            $data = $request->except(['_token', '_method']);
        }

        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }

            $group = null;
            if (str_starts_with($key, 'queue.maintenance')) {
                $group = 'maintenance';
            } elseif (str_starts_with($key, 'queue.open') || str_starts_with($key, 'queue.close') || $key === 'queue.registration_open_days_ahead') {
                $group = 'schedule';
            } elseif ($key === 'queue.default_daily_quota') {
                $group = 'quota';
            } elseif (str_contains($key, 'cancellation') || str_contains($key, 'deadline') || str_contains($key, 'timeout')) {
                $group = 'policy';
            } elseif (str_contains($key, 'notify')) {
                $group = 'notification';
            } elseif (str_contains($key, 'address') || str_contains($key, 'phone')) {
                $group = 'general';
            }

            QueueSetting::set($key, (string) $value, $group);
        }

        return back()->with('success', 'Pengaturan antrian online berhasil disimpan.');
    }
}
