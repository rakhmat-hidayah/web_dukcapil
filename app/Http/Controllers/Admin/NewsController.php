<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Intervention\Image\Laravel\Facades\Image;

class NewsController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = News::with(['category', 'author'])
            ->withTrashed()
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('news_category_id', $request->category);
        }

        $news = $query->paginate(15)->withQueryString();

        $categories = NewsCategory::orderBy('name')->get(['id', 'name', 'color']);

        return Inertia::render('Admin/News/Index', [
            'news' => $news,
            'categories' => $categories,
            'filters' => $request->only(['search', 'status', 'category']),
        ]);
    }

    public function create(): InertiaResponse
    {
        $categories = NewsCategory::orderBy('sort_order')->get(['id', 'name', 'color']);
        $tags = NewsTag::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/News/Form', [
            'categories' => $categories,
            'tags' => $tags,
            'news' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,scheduled,published',
            'news_category_id' => 'nullable|exists:news_categories,id',
            'thumbnail' => 'nullable|image|max:5120',
            'published_at' => 'nullable|date',
        ]);

        $data = $request->except(['thumbnail', 'tag_ids']);
        $data['slug'] = Str::slug($request->title);
        $data['user_id'] = auth()->id();
        $data['excerpt'] = $request->excerpt ?: Str::limit(strip_tags($request->content), 200);

        if ($request->status === 'published' && !$request->published_at) {
            $data['published_at'] = now();
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $img = Image::decode($request->file('thumbnail'))->scale(width: 1200);
            $path = 'news/' . date('Y/m') . '/' . Str::uuid() . '.webp';
            Storage::disk('public')->put($path, $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 80)));
            $data['thumbnail'] = $path;
        }

        $news = News::create($data);

        // Sync tags
        if ($request->filled('tag_ids')) {
            $news->tags()->sync($request->tag_ids);
        }

        ActivityLogger::log("Membuat artikel berita: {$news->title}", $news, 'create_news');

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil disimpan.');
    }

    public function edit(News $news): InertiaResponse
    {
        $news->load(['tags', 'category']);
        $categories = NewsCategory::orderBy('sort_order')->get(['id', 'name', 'color']);
        $tags = NewsTag::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/News/Form', [
            'categories' => $categories,
            'tags' => $tags,
            'news' => array_merge($news->toArray(), [
                'tag_ids' => $news->tags->pluck('id')->toArray(),
            ]),
        ]);
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,scheduled,published',
            'news_category_id' => 'nullable|exists:news_categories,id',
            'thumbnail' => 'nullable|image|max:5120',
            'published_at' => 'nullable|date',
        ]);

        $data = $request->except(['thumbnail', 'tag_ids']);
        $data['excerpt'] = $request->excerpt ?: Str::limit(strip_tags($request->content), 200);

        if ($request->status === 'published' && !$news->published_at && !$request->published_at) {
            $data['published_at'] = now();
        }

        // Handle thumbnail
        if ($request->boolean('remove_thumbnail')) {
            if ($news->thumbnail) {
                Storage::disk('public')->delete($news->thumbnail);
            }
            $data['thumbnail'] = null;
        } elseif ($request->hasFile('thumbnail')) {
            if ($news->thumbnail) {
                Storage::disk('public')->delete($news->thumbnail);
            }
            $img = Image::decode($request->file('thumbnail'))->scale(width: 1200);
            $path = 'news/' . date('Y/m') . '/' . Str::uuid() . '.webp';
            Storage::disk('public')->put($path, $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 80)));
            $data['thumbnail'] = $path;
        }

        $news->update($data);
        $news->tags()->sync($request->tag_ids ?? []);

        ActivityLogger::log("Memperbarui artikel berita: {$news->title}", $news, 'update_news');

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $title = $news->title;
        $news->delete();

        ActivityLogger::log("Menghapus berita: {$title}", null, 'delete_news');

        return redirect()->back()->with('success', 'Berita berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $news = News::withTrashed()->findOrFail($id);
        $news->restore();

        return redirect()->back()->with('success', 'Berita berhasil dipulihkan.');
    }

    // --- Categories CRUD ---

    public function categories(): InertiaResponse
    {
        $categories = NewsCategory::withCount('news')->orderBy('sort_order')->get();

        return Inertia::render('Admin/News/Categories', [
            'categories' => $categories,
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:100', 'color' => 'nullable|string|max:20']);
        NewsCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color' => $request->color ?? '#3b82f6',
        ]);
        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, NewsCategory $newsCategory): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:100', 'color' => 'nullable|string|max:20']);
        $newsCategory->update(['name' => $request->name, 'color' => $request->color]);
        return redirect()->back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyCategory(NewsCategory $newsCategory): RedirectResponse
    {
        $newsCategory->delete();
        return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
    }

    public function uploadInlineImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:10240',
        ]);

        $img = Image::decode($request->file('image'))->scaleDown(width: 1200);
        $path = 'news/inline/' . date('Y/m') . '/' . Str::uuid() . '.webp';
        Storage::disk('public')->put($path, (string) $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 85)));

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }
}

