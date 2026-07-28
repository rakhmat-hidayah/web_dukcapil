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
            'cover_image' => 'nullable|image|max:5120',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $img = Image::decode($file)->cover(600, 400);
            $coverPath = 'gallery/covers/' . Str::uuid() . '.webp';
            \App\Services\MediaStorage::put($coverPath, (string) $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 80)));
        }

        GalleryAlbum::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'type' => $request->type,
            'cover_image' => $coverPath,
            'is_published' => $request->boolean('is_published', true),
        ]);

        return redirect()->back()->with('success', 'Album berhasil dibuat.');
    }

    public function updateAlbum(Request $request, GalleryAlbum $galleryAlbum): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'cover_image' => 'nullable|image|max:5120',
        ]);

        $data = $request->only(['title', 'description']);
        $data['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('cover_image')) {
            if ($galleryAlbum->cover_image) {
                \App\Services\MediaStorage::delete($galleryAlbum->cover_image);
            }
            $file = $request->file('cover_image');
            $img = Image::decode($file)->cover(600, 400);
            $coverPath = 'gallery/covers/' . Str::uuid() . '.webp';
            \App\Services\MediaStorage::put($coverPath, (string) $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 80)));
            $data['cover_image'] = $coverPath;
        }

        $galleryAlbum->update($data);
        return redirect()->back()->with('success', 'Album berhasil diperbarui.');
    }

    public function destroyAlbum(GalleryAlbum $galleryAlbum): RedirectResponse
    {
        // Delete all items
        foreach ($galleryAlbum->items as $item) {
            if ($item->file_path) \App\Services\MediaStorage::delete($item->file_path);
            if ($item->thumbnail) \App\Services\MediaStorage::delete($item->thumbnail);
        }
        if ($galleryAlbum->cover_image) \App\Services\MediaStorage::delete($galleryAlbum->cover_image);
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
            $filePath = str_replace('.' . $file->getClientOriginalExtension(), '.webp', $filePath);
            \App\Services\MediaStorage::put($filePath, (string) $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 80)));

            // Generate thumbnail
            $thumbPath = 'gallery/' . $galleryAlbum->id . '/thumb-' . Str::uuid() . '.webp';
            $thumb = Image::decode($file)->cover(400, 300);
            \App\Services\MediaStorage::put($thumbPath, (string) $thumb->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 75)));
            $thumbnailPath = $thumbPath;
        } else {
            $filePath = \App\Services\MediaStorage::store($file, 'gallery/' . $galleryAlbum->id);
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
        if ($galleryItem->file_path) \App\Services\MediaStorage::delete($galleryItem->file_path);
        if ($galleryItem->thumbnail) \App\Services\MediaStorage::delete($galleryItem->thumbnail);
        $galleryItem->delete();
        return redirect()->back()->with('success', 'Media berhasil dihapus.');
    }
}

