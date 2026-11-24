<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SplashScreen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SplashScreenController extends Controller
{
    public function index(): InertiaResponse
    {
        $splashScreens = SplashScreen::orderBy('sort_order')->orderByDesc('created_at')->get()
            ->map(fn($s) => array_merge($s->toArray(), [
                'status_label' => $s->status_label,
                'is_currently_active' => $s->isCurrentlyActive(),
            ]));

        return Inertia::render('Admin/SplashScreens/Index', [
            'splashScreens' => $splashScreens,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'subtitle'       => 'nullable|string|max:500',
            'content_type'   => 'required|in:ikm_widget,announcement,image,custom',
            'custom_content' => 'nullable|string',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
            'button_label'   => 'nullable|string|max:100',
            'button_url'     => 'nullable|url|max:500',
            'button_external'=> 'nullable|in:0,1,true,false',
            'show_frequency' => 'required|in:once_per_session,once_per_day,always',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'is_active'      => 'nullable|in:0,1,true,false',
            'sort_order'     => 'nullable|integer|min:0',
        ]);

        $data['button_external'] = filter_var($data['button_external'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['is_active']       = filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('splash-screens', 'public');
        }
        unset($data['image']);

        SplashScreen::create($data);

        return redirect()->route('admin.splash-screens.index')
            ->with('success', 'Splash Screen berhasil ditambahkan.');
    }

    public function update(Request $request, SplashScreen $splashScreen): RedirectResponse
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'subtitle'       => 'nullable|string|max:500',
            'content_type'   => 'required|in:ikm_widget,announcement,image,custom',
            'custom_content' => 'nullable|string',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
            'button_label'   => 'nullable|string|max:100',
            'button_url'     => 'nullable|url|max:500',
            'button_external'=> 'nullable|in:0,1,true,false',
            'show_frequency' => 'required|in:once_per_session,once_per_day,always',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'is_active'      => 'nullable|in:0,1,true,false',
            'sort_order'     => 'nullable|integer|min:0',
        ]);

        $data['button_external'] = filter_var($data['button_external'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['is_active']       = filter_var($data['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($request->hasFile('image')) {
            // Hapus gambar lama
            if ($splashScreen->image_path) {
                Storage::disk('public')->delete($splashScreen->image_path);
            }
            $data['image_path'] = $request->file('image')->store('splash-screens', 'public');
        }
        unset($data['image']);

        $splashScreen->update($data);

        return redirect()->route('admin.splash-screens.index')
            ->with('success', 'Splash Screen berhasil diperbarui.');
    }

    public function destroy(SplashScreen $splashScreen): RedirectResponse
    {
        if ($splashScreen->image_path) {
            Storage::disk('public')->delete($splashScreen->image_path);
        }
        $splashScreen->delete();

        return redirect()->route('admin.splash-screens.index')
            ->with('success', 'Splash Screen berhasil dihapus.');
    }

    public function toggleActive(SplashScreen $splashScreen): RedirectResponse
    {
        $splashScreen->update(['is_active' => !$splashScreen->is_active]);

        return redirect()->route('admin.splash-screens.index')
            ->with('success', $splashScreen->is_active ? 'Splash Screen diaktifkan.' : 'Splash Screen dinonaktifkan.');
    }
}
