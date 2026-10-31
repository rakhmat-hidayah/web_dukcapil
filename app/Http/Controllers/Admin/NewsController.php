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

class NewsController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = News::with(['category', 'author'])
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
        
        // Transform items: use Base64 if available to 100% bypass Cloudflare WAF, fallback to /uploads/
        $news->getCollection()->transform(function ($item) {
            if ($item->thumbnail_base64) {
                $item->thumbnail = $item->thumbnail_base64;
            } elseif ($item->thumbnail) {
                $clean = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $item->thumbnail), '/');
                $item->thumbnail = '/uploads/' . $clean;
            }
            return $item;
        });

        $categories = NewsCategory::orderBy('name')->get(['id', 'name', 'color']);

        try {
            $hasLogsTable = \Illuminate\Support\Facades\Schema::hasTable('article_reader_logs');
            $hasPageViewsCol = \Illuminate\Support\Facades\Schema::hasColumn('news', 'page_views');

            $today = now()->startOfDay();
            $thisWeek = now()->startOfWeek();
            $thisMonth = now()->startOfMonth();

            $totalPageViews = $hasPageViewsCol ? (int) News::sum('page_views') : (int) News::sum('view_count');
            $totalUniqueReaders = \Illuminate\Support\Facades\Schema::hasColumn('news', 'unique_readers') ? (int) News::sum('unique_readers') : (int) News::sum('view_count');

            $humanCount = $hasLogsTable ? \App\Models\ArticleReaderLog::where('is_robot', false)->count() : 0;
            $robotHits = \Illuminate\Support\Facades\Schema::hasColumn('news', 'robot_hits') ? (int) News::sum('robot_hits') : 0;

            $todayViews = $hasLogsTable ? \App\Models\ArticleReaderLog::where('is_robot', false)->where('last_read_at', '>=', $today)->count() : 0;
            $weekViews = $hasLogsTable ? \App\Models\ArticleReaderLog::where('is_robot', false)->where('last_read_at', '>=', $thisWeek)->count() : 0;
            $monthViews = $hasLogsTable ? \App\Models\ArticleReaderLog::where('is_robot', false)->where('last_read_at', '>=', $thisMonth)->count() : 0;

            $totalBeneficial = (int) News::sum('beneficial_count');
            $totalInformative = (int) News::sum('informative_count');
            $totalEngagement = $totalBeneficial + $totalInformative;
            $reactionPercent = $totalUniqueReaders > 0 ? round(($totalEngagement / $totalUniqueReaders) * 100) : 0;

            $readerStats = [
                'total_page_views'        => $totalPageViews ?: (int) News::sum('view_count'),
                'total_unique_readers'    => $totalUniqueReaders ?: (int) News::sum('view_count'),
                'total_human_readers'     => $humanCount,
                'total_robot_hits'        => $robotHits,
                'today_views'             => $todayViews,
                'week_views'              => $weekViews,
                'month_views'             => $monthViews,
                'total_beneficial'        => $totalBeneficial,
                'total_informative'       => $totalInformative,
                'total_engagement'        => $totalEngagement,
                'reaction_percent'        => $reactionPercent,
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Reader stats fallback: ' . $e->getMessage());
            $readerStats = [
                'total_page_views'        => (int) News::sum('view_count'),
                'total_unique_readers'    => (int) News::sum('view_count'),
                'total_human_readers'     => 0,
                'total_robot_hits'        => 0,
                'today_views'             => 0,
                'week_views'              => 0,
                'month_views'             => 0,
                'total_beneficial'        => 0,
                'total_informative'       => 0,
                'total_engagement'        => 0,
                'reaction_percent'        => 0,
            ];
        }

        return Inertia::render('Admin/News/Index', [
            'news' => $news,
            'categories' => $categories,
            'readerStats' => $readerStats,
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
            'thumbnail' => 'nullable|image|max:10240',
            'thumbnail_caption' => 'nullable|string|max:500',
            'thumbnail_alt' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
            'is_featured' => 'nullable|boolean',
            'is_breaking' => 'nullable|boolean',
        ], [
            'thumbnail.max' => 'Ukuran file gambar cover terlalu besar. Maksimal 10 MB.',
            'thumbnail.image' => 'Berkas cover harus berupa file gambar (JPG, PNG, WebP).',
            'content.required' => 'Isi artikel berita tidak boleh kosong.',
            'title.required' => 'Judul artikel berita tidak boleh kosong.',
        ]);

        $data = $request->except(['thumbnail', 'tag_ids', 'custom_tags']);
        $data['slug'] = Str::slug($request->title);
        $data['user_id'] = auth()->id();
        $data['excerpt'] = $request->excerpt ?: Str::limit(strip_tags($request->content), 200);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_breaking'] = $request->boolean('is_breaking');

        if ($request->status === 'published') {
            if ($request->filled('published_at')) {
                $data['published_at'] = \Illuminate\Support\Carbon::parse($request->published_at);
            } else {
                $data['published_at'] = now();
            }
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $beritaFolder = \App\Models\FileFolder::firstOrCreate(
                ['name' => 'Berita'],
                ['parent_id' => null, 'created_by' => auth()->id()]
            );
            $fileEntry = \App\Services\FileManagerService::uploadFile(
                $request->file('thumbnail'),
                $beritaFolder->id,
                auth()->id()
            );
            $data['thumbnail'] = $fileEntry->path;
        }

        $news = News::create($data);

        // Sync tags (both existing checklist tag_ids and custom_tags input)
        $tagIds = is_array($request->tag_ids) ? $request->tag_ids : [];
        if ($request->filled('custom_tags')) {
            $rawTags = array_filter(array_map('trim', explode(',', $request->custom_tags)));
            foreach ($rawTags as $tagName) {
                if (!empty($tagName)) {
                    $t = NewsTag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $t->id;
                }
            }
        }
        $news->tags()->sync(array_unique($tagIds));

        ActivityLogger::log("Membuat artikel berita: {$news->title}", $news, 'create_news');

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil disimpan.');
    }

    public function edit(News $news): InertiaResponse
    {
        $news->load(['tags', 'category']);
        $categories = NewsCategory::orderBy('sort_order')->get(['id', 'name', 'color']);
        $tags = NewsTag::orderBy('name')->get(['id', 'name']);

        if ($news->thumbnail) {
            $news->thumbnail = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $news->thumbnail), '/');
        }

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
            'thumbnail' => 'nullable|image|max:10240',
            'thumbnail_caption' => 'nullable|string|max:500',
            'thumbnail_alt' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
            'is_featured' => 'nullable|boolean',
            'is_breaking' => 'nullable|boolean',
        ], [
            'thumbnail.max' => 'Ukuran file gambar cover terlalu besar. Maksimal 10 MB.',
            'thumbnail.image' => 'Berkas cover harus berupa file gambar (JPG, PNG, WebP).',
            'content.required' => 'Isi artikel berita tidak boleh kosong.',
            'title.required' => 'Judul artikel berita tidak boleh kosong.',
        ]);

        $data = $request->except(['thumbnail', 'tag_ids', 'custom_tags']);
        $data['last_updated_user_id'] = auth()->id();
        $data['excerpt'] = $request->excerpt ?: Str::limit(strip_tags($request->content), 200);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_breaking'] = $request->boolean('is_breaking');

        if ($request->status === 'published') {
            if ($request->filled('published_at')) {
                $data['published_at'] = \Illuminate\Support\Carbon::parse($request->published_at);
            } else {
                $data['published_at'] = $news->published_at ?: now();
            }
        }

        // Handle thumbnail
        if ($request->boolean('remove_thumbnail')) {
            if ($news->thumbnail) {
                \App\Services\MediaStorage::delete($news->thumbnail);
            }
            $data['thumbnail'] = null;
        } elseif ($request->hasFile('thumbnail')) {
            if ($news->thumbnail) {
                \App\Services\MediaStorage::delete($news->thumbnail);
            }
            $beritaFolder = \App\Models\FileFolder::firstOrCreate(
                ['name' => 'Berita'],
                ['parent_id' => null, 'created_by' => auth()->id()]
            );
            $fileEntry = \App\Services\FileManagerService::uploadFile(
                $request->file('thumbnail'),
                $beritaFolder->id,
                auth()->id()
            );
            $data['thumbnail'] = $fileEntry->path;
        }

        $news->update($data);

        // Sync tags (both existing checklist tag_ids and custom_tags input)
        $tagIds = is_array($request->tag_ids) ? $request->tag_ids : [];
        if ($request->filled('custom_tags')) {
            $rawTags = array_filter(array_map('trim', explode(',', $request->custom_tags)));
            foreach ($rawTags as $tagName) {
                if (!empty($tagName)) {
                    $t = NewsTag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $t->id;
                }
            }
        }
        $news->tags()->sync(array_unique($tagIds));

        ActivityLogger::log("Memperbarui artikel berita: {$news->title}", $news, 'update_news');

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $title = $news->title;
        if ($news->thumbnail) {
            \App\Services\MediaStorage::delete($news->thumbnail);
        }
        $news->forceDelete();

        ActivityLogger::log("Menghapus berita: {$title}", null, 'delete_news');

        return redirect()->back()->with('success', 'Berita berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $news = News::withTrashed()->findOrFail($id);
        $news->restore();

        return redirect()->back()->with('success', 'Berita berhasil dipulihkan.');
    }

    /**
     * Get Social Share Analytics Data for Admin Dashboard.
     */
    public function shareAnalytics(): \Illuminate\Http\JsonResponse
    {
        // Auto-run pending database migrations if news_shares table or social columns are missing on hosting DB
        try {
            if (!Schema::hasTable('news_shares') || !Schema::hasColumn('news', 'share_count')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Auto migrate in shareAnalytics error: ' . $e->getMessage());
        }

        $hasSharesTable = false;
        try {
            $hasSharesTable = Schema::hasTable('news_shares');
        } catch (\Throwable $e) {}

        $hasShareCountCol = false;
        try {
            $hasShareCountCol = Schema::hasColumn('news', 'share_count');
        } catch (\Throwable $e) {}

        // 1. Total shares & CTR
        $totalShares = 0;
        try {
            if ($hasShareCountCol) {
                $totalShares = (int) News::sum('share_count');
            }
        } catch (\Throwable $e) {}

        $totalViews = 0;
        try {
            $totalViews = (int) News::sum('view_count');
        } catch (\Throwable $e) {}

        $ctrShare = $totalViews > 0 ? round(($totalShares / $totalViews) * 100, 2) : 0;

        // 2. Daily & Monthly Share Logs
        $sharesToday = 0;
        $sharesThisMonth = 0;
        try {
            if ($hasSharesTable) {
                $sharesToday = \App\Models\NewsShare::whereDate('created_at', now()->today())->count();
                $sharesThisMonth = \App\Models\NewsShare::whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('NewsShare query error: ' . $e->getMessage());
        }

        // 3. Top Shared News Articles
        $topShared = collect();
        try {
            if ($hasShareCountCol) {
                $topShared = News::select(['id', 'title', 'slug', 'view_count', 'share_count', 'whatsapp_shares', 'facebook_shares', 'telegram_shares', 'twitter_shares', 'linkedin_shares', 'copy_link_count'])
                    ->where('share_count', '>', 0)
                    ->orderBy('share_count', 'desc')
                    ->limit(10)
                    ->get();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('topShared query error: ' . $e->getMessage());
        }

        // 4. Platform Breakdown
        $platformBreakdown = [
            'whatsapp'  => 0,
            'facebook'  => 0,
            'telegram'  => 0,
            'twitter'   => 0,
            'linkedin'  => 0,
            'copy_link' => 0,
        ];

        try {
            $platformBreakdown = [
                'whatsapp'  => Schema::hasColumn('news', 'whatsapp_shares') ? (int) News::sum('whatsapp_shares') : 0,
                'facebook'  => Schema::hasColumn('news', 'facebook_shares') ? (int) News::sum('facebook_shares') : 0,
                'telegram'  => Schema::hasColumn('news', 'telegram_shares') ? (int) News::sum('telegram_shares') : 0,
                'twitter'   => Schema::hasColumn('news', 'twitter_shares') ? (int) News::sum('twitter_shares') : 0,
                'linkedin'  => Schema::hasColumn('news', 'linkedin_shares') ? (int) News::sum('linkedin_shares') : 0,
                'copy_link' => Schema::hasColumn('news', 'copy_link_count') ? (int) News::sum('copy_link_count') : 0,
            ];
        } catch (\Throwable $e) {}

        return response()->json([
            'total_shares'      => $totalShares,
            'shares_today'      => $sharesToday,
            'shares_this_month' => $sharesThisMonth,
            'ctr_share'         => $ctrShare,
            'top_shared_news'   => $topShared,
            'platform_breakdown'=> $platformBreakdown,
        ]);
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
        try {
            $path = null;

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $file = $request->file('image');
                $beritaFolder = \App\Models\FileFolder::firstOrCreate(
                    ['name' => 'Berita'],
                    ['parent_id' => null, 'created_by' => auth()->id()]
                );

                $fileEntry = \App\Services\FileManagerService::uploadFile(
                    $file,
                    $beritaFolder->id,
                    auth()->id()
                );
                $path = \App\Services\MediaStorage::getUrl($fileEntry->path);
            } elseif ($request->filled('image_base64')) {
                $base64Data = $request->image_base64;
                $originalName = $request->image_name ?? 'berita-photo.jpg';
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg');

                if (str_contains($base64Data, ';base64,')) {
                    $base64Data = explode(';base64,', $base64Data)[1];
                }

                $binaryData = base64_decode($base64Data);
                $filename = 'berita-' . date('Ymd-His') . '-' . Str::random(6) . '.' . $extension;
                
                $savedPath = \App\Services\MediaStorage::put("berita/{$filename}", $binaryData);
                $path = \App\Services\MediaStorage::getUrl($savedPath);
            } else {
                return response()->json(['message' => 'Berkas gambar tidak terdeteksi atau melebihi batas server.'], 422);
            }

            return response()->json([
                'url' => $path,
                'success' => true,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Upload inline image error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengunggah foto: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get comprehensive news reader analytics for Admin Dashboard.
     */
    public function readerAnalytics()
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        $humanQuery = \App\Models\ArticleReaderLog::where('is_robot', false);
        $robotQuery = \App\Models\ArticleReaderLog::where('is_robot', true);

        $totalPageViews = (int) (News::sum('page_views') ?: News::sum('view_count'));
        $totalUniqueReaders = (int) (News::sum('unique_readers') ?: $humanQuery->count());
        $totalHumanReaders = (int) $humanQuery->count();
        $totalRobotHits = (int) (News::sum('robot_hits') ?: $robotQuery->count());

        $todayViews = (int) (clone $humanQuery)->where('last_read_at', '>=', $today)->count();
        $weekViews  = (int) (clone $humanQuery)->where('last_read_at', '>=', $thisWeek)->count();
        $monthViews = (int) (clone $humanQuery)->where('last_read_at', '>=', $thisMonth)->count();

        $trendingArticles = News::with('category')
            ->orderByDesc('unique_readers')
            ->orderByDesc('page_views')
            ->limit(6)
            ->get(['id', 'title', 'slug', 'unique_readers', 'page_views', 'view_count', 'published_at', 'news_category_id']);

        $totalBeneficial = (int) News::sum('beneficial_count');
        $totalInformative = (int) News::sum('informative_count');
        $totalEngagement = $totalBeneficial + $totalInformative;
        $reactionPercent = $totalUniqueReaders > 0 ? round(($totalEngagement / $totalUniqueReaders) * 100) : 0;

        return response()->json([
            'total_page_views'        => $totalPageViews,
            'total_unique_readers'    => $totalUniqueReaders,
            'total_human_readers'     => $totalHumanReaders,
            'total_robot_hits'        => $totalRobotHits,
            'today_views'             => $todayViews,
            'week_views'              => $weekViews,
            'month_views'             => $monthViews,
            'total_beneficial'        => $totalBeneficial,
            'total_informative'       => $totalInformative,
            'total_engagement'        => $totalEngagement,
            'reaction_percent'        => $reactionPercent,
            'trending_articles'    => $trendingArticles,
        ]);
    }

    /**
     * Search tags for autocomplete tag selector in CMS.
     */
    public function searchTags(Request $request)
    {
        $q = trim($request->input('q', ''));
        $tags = NewsTag::when(!empty($q), function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                  ->orWhere('slug', 'like', "%{$q}%");
        })->orderBy('name')->limit(20)->get(['id', 'name', 'slug']);

        return response()->json($tags);
    }
}
