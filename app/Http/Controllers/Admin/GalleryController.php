<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use App\Models\GalleryItem;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Intervention\Image\Laravel\Facades\Image;

class GalleryController extends Controller
{
    public function index(): InertiaResponse
    {
        $albums = GalleryAlbum::withCount('items')->orderBy('sort_order')->get();

        return Inertia::render('Admin/Gallery/Index', [
            'albums' => $albums,
        ]);
    }

    public function show(GalleryAlbum $galleryAlbum): InertiaResponse
    {
        $galleryAlbum->load('items');

        return Inertia::render('Admin/Gallery/Show', [
            'album' => $galleryAlbum,
        ]);
    }

    public function storeAlbum(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:photo,video',
        ]);

        GalleryAlbum::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'type' => $request->type,
            'is_published' => $request->boolean('is_published', true),
        ]);

        return redirect()->back()->with('success', 'Album berhasil dibuat.');
    }

    public function updateAlbum(Request $request, GalleryAlbum $galleryAlbum): RedirectResponse
    {
        $request->validate(['title' => 'required|string|max:255']);
        $galleryAlbum->update($request->only(['title', 'description', 'is_published']));
        return redirect()->back()->with('success', 'Album berhasil diperbarui.');
    }

    public function destroyAlbum(GalleryAlbum $galleryAlbum): RedirectResponse
    {
        // Delete all items
        foreach ($galleryAlbum->items as $item) {
            Storage::disk('public')->delete([$item->file_path, $item->thumbnail]);
        }
        $galleryAlbum->delete();
        return redirect()->back()->with('success', 'Album berhasil dihapus.');
    }

    public function uploadItem(Request $request, GalleryAlbum $galleryAlbum): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,gif,webp,mp4,mov|max:51200',
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $mimeType = $file->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');

        $filePath = 'gallery/' . $galleryAlbum->id . '/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        $thumbnailPath = null;

        if (!$isVideo) {
            $img = Image::decode($file)->scale(width: 1600);
            Storage::disk('public')->put($filePath, $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 80)));

            // Generate thumbnail
            $thumbPath = 'gallery/' . $galleryAlbum->id . '/thumb-' . Str::uuid() . '.webp';
            $thumb = Image::decode($file)->cover(400, 300);
            Storage::disk('public')->put($thumbPath, $thumb->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 75)));
            $thumbnailPath = $thumbPath;
            $filePath = str_replace('.' . $file->getClientOriginalExtension(), '.webp', $filePath);
        } else {
            Storage::disk('public')->putFileAs(
                dirname($filePath),
                $file,
                basename($filePath)
            );
        }

        GalleryItem::create([
            'gallery_album_id' => $galleryAlbum->id,
            'title' => $request->title,
            'file_path' => $filePath,
            'file_type' => $isVideo ? 'video' : 'image',
            'thumbnail' => $thumbnailPath,
            'caption' => $request->caption,
        ]);

        // Update album cover if no cover set
        if (!$galleryAlbum->cover_image && $thumbnailPath) {
            $galleryAlbum->update(['cover_image' => $thumbnailPath]);
        }

        ActivityLogger::log("Upload media ke album: {$galleryAlbum->title}", $galleryAlbum, 'upload_gallery');

        return redirect()->back()->with('success', 'Media berhasil diupload.');
    }

    public function destroyItem(GalleryItem $galleryItem): RedirectResponse
    {
        Storage::disk('public')->delete([$galleryItem->file_path, $galleryItem->thumbnail]);
        $galleryItem->delete();
        return redirect()->back()->with('success', 'Media berhasil dihapus.');
    }
}

