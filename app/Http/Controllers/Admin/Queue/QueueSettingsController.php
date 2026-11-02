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

        return Inertia::render('Admin/Queue/Settings/Index', [
            'settings' => $settings,
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
            QueueSetting::set($key, (string) $value);
        }

        return back()->with('success', 'Pengaturan antrian online berhasil disimpan.');
    }
}
