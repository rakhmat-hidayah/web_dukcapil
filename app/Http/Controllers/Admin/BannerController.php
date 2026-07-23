<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Intervention\Image\Laravel\Facades\Image;

class BannerController extends Controller
{
    public function index(): InertiaResponse
    {
        $banners = Banner::orderBy('sort_order')->get();

        return Inertia::render('Admin/Banners/Index', [
            'banners' => $banners,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:hero,popup,sidebar,campaign',
            'image' => 'required|image|max:10240',
            'url' => 'nullable|url',
        ]);

        $data = $request->except(['image', 'image_mobile']);

        // Upload main image
        $img = Image::decode($request->file('image'))->scale(width: 1920);
        $path = 'banners/' . Str::uuid() . '.webp';
        Storage::disk('public')->put($path, $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 85)));
        $data['image'] = $path;

        // Upload mobile image if provided
        if ($request->hasFile('image_mobile')) {
            $mobileImg = Image::decode($request->file('image_mobile'))->scale(width: 768);
            $mobilePath = 'banners/mobile-' . Str::uuid() . '.webp';
            Storage::disk('public')->put($mobilePath, $mobileImg->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 85)));
            $data['image_mobile'] = $mobilePath;
        }

        $banner = Banner::create($data);

        ActivityLogger::log("Menambahkan banner: {$banner->title}", $banner, 'create_banner');

        return redirect()->back()->with('success', 'Banner berhasil ditambahkan.');
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:hero,popup,sidebar,campaign',
        ]);

        $data = $request->except(['image', 'image_mobile']);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image);
            $img = Image::decode($request->file('image'))->scale(width: 1920);
            $path = 'banners/' . Str::uuid() . '.webp';
            Storage::disk('public')->put($path, $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 85)));
            $data['image'] = $path;
        }

        $banner->update($data);

        ActivityLogger::log("Memperbarui banner: {$banner->title}", $banner, 'update_banner');

        return redirect()->back()->with('success', 'Banner berhasil diperbarui.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        foreach ($request->order as $item) {
            Banner::where('id', $item['id'])->update(['sort_order' => $item['sort']]);
        }
        return redirect()->back();
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        Storage::disk('public')->delete([$banner->image, $banner->image_mobile]);
        $banner->delete();
        return redirect()->back()->with('success', 'Banner berhasil dihapus.');
    }
}

