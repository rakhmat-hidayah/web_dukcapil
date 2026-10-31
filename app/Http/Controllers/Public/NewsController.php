<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ArticleReaction;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Services\NewsSeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class NewsController extends Controller
{
    /**
     * Display public news feed listing.
     */
    public function index(Request $request): InertiaResponse
    {
        $query = News::with(['category', 'author'])
            ->published()
            ->orderBy('published_at', 'desc');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        $news = $query->paginate(9)->withQueryString();

        $news->getCollection()->transform(function ($item) {
            if ($item->thumbnail_base64) {
                $item->thumbnail = $item->thumbnail_base64;
            } elseif ($item->thumbnail) {
                $clean = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $item->thumbnail), '/');
                $item->thumbnail = '/uploads/' . $clean;
            }
            return $item;
        });

        $categories = NewsCategory::orderBy('sort_order')->get(['id', 'name', 'slug', 'color']);

        return Inertia::render('Public/News/Index', [
            'news' => $news,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    /**
     * Filter public news feed by Tag.
     */
    public function tagArchive(Request $request, string $slug)
    {
        try {
            if (!Schema::hasTable('news_tags') || !Schema::hasTable('news_news_tags')) {
                return redirect()->route('public.news.index');
            }

            $tag = NewsTag::where('slug', $slug)->first();
            if (!$tag) {
                return redirect()->route('public.news.index');
            }

            $query = News::with(['category', 'author'])
                ->published()
                ->whereHas('tags', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                })
                ->orderBy('published_at', 'desc');

            $news = $query->paginate(9)->withQueryString();

            $news->getCollection()->transform(function ($item) {
                if ($item->thumbnail_base64) {
                    $item->thumbnail = $item->thumbnail_base64;
                } elseif ($item->thumbnail) {
                    $clean = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $item->thumbnail), '/');
                    $item->thumbnail = '/uploads/' . $clean;
                }
                return $item;
            });

            $categories = NewsCategory::orderBy('sort_order')->get(['id', 'name', 'slug', 'color']);

            return Inertia::render('Public/News/Index', [
                'news' => $news,
                'categories' => $categories,
                'currentTag' => $tag,
                'filters' => ['tag' => $slug],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Tag archive fallback error: ' . $e->getMessage());
            return redirect()->route('public.news.index');
        }
    }

    /**
     * Display specific news details with full SSR Open Graph metadata.
     */
    public function show(Request $request, string $slug): InertiaResponse
    {
        $relations = ['category', 'author'];
        try {
            if (Schema::hasTable('news_tags') && Schema::hasTable('news_news_tags')) {
                $relations[] = 'tags';
            }
        } catch (\Throwable $e) {}

        $item = News::with($relations)
            ->published()
            ->where('slug', $slug)
            ->first();

        if (!$item) {
            abort(404, 'Artikel berita tidak ditemukan atau telah dihapus.');
        }

        if ($item->thumbnail_base64) {
            $item->thumbnail = $item->thumbnail_base64;
        } elseif ($item->thumbnail) {
            $clean = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $item->thumbnail), '/');
            $item->thumbnail = '/uploads/' . $clean;
        }

        // Increment views & unique readers count safely with Bot filtering & 24h TTL
        try {
            $item->incrementViews($request);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Increment views fallback: ' . $e->getMessage());
        }

        // Visitor UUID for current reaction check
        $userReaction = null;
        try {
            $visitorUuid = $request->cookie('visitor_uuid');
            if ($visitorUuid && Schema::hasTable('article_reactions')) {
                $userReaction = ArticleReaction::where('news_id', $item->id)
                    ->where('visitor_uuid', $visitorUuid)
                    ->value('reaction_type');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('User reaction lookup error: ' . $e->getMessage());
            $userReaction = null;
        }

        // Smart Previous & Next Navigation Calculation
        $navigation = ['previous' => null, 'next' => null];
        try {
            $navigation = $this->getPrevNextArticles($item);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Navigation resolution error: ' . $e->getMessage());
        }

        // Retrieve related articles
        $related = collect();
        try {
            $related = News::with('category')
                ->published()
                ->where('id', '!=', $item->id)
                ->where('news_category_id', $item->news_category_id)
                ->limit(3)
                ->get();

            $related->transform(function ($rel) {
                if ($rel->thumbnail) {
                    $clean = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $rel->thumbnail), '/');
                    $rel->thumbnail = '/uploads/' . $clean;
                }
                return $rel;
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Related news lookup error: ' . $e->getMessage());
        }

        // Generate full SEO, Open Graph & Schema Metadata via NewsMetaService (Single Source of Truth)
        try {
            $seoData = \App\Services\NewsMetaService::generate($item);
            View::share('seoData', $seoData);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('SEO Metadata generation error: ' . $e->getMessage());
            $seoData = null;
        }

        $breadcrumbs = $seoData['breadcrumbs'] ?? [
            ['label' => 'Beranda',  'url' => url('/'),                                        'active' => false],
            ['label' => 'Berita',   'url' => route('public.news.index'),                       'active' => false],
            ['label' => $item->title, 'url' => request()->url(),                               'active' => true],
        ];

        return Inertia::render('Public/News/Show', [
            'news'         => $item,
            'userReaction' => $userReaction,
            'navigation'   => $navigation,
            'related'      => $related,
            'seo'          => $seoData,
            'breadcrumbs'  => $breadcrumbs,
        ]);
    }

    /**
     * Smart Previous & Next Article Resolution:
     * Priority: Same Category -> Same Tag -> Published Date / ID order
     */
    private function getPrevNextArticles(News $item): array
    {
        try {
            $publishedAt = $item->published_at ?? $item->created_at;

            // Base Query
            $baseQuery = News::with('category')->published()->where('id', '!=', $item->id);

            // 1. Previous Article Resolution (Older than current)
            $prev = (clone $baseQuery)
                ->where('news_category_id', $item->news_category_id)
                ->where(function ($q) use ($publishedAt, $item) {
                    $q->where('published_at', '<', $publishedAt)
                      ->orWhere(function ($q2) use ($publishedAt, $item) {
                          $q2->where('published_at', $publishedAt)->where('id', '<', $item->id);
                      });
                })
                ->orderBy('published_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $hasTags = Schema::hasTable('news_tags') && Schema::hasTable('news_news_tags');

            if (!$prev && $hasTags && $item->relationLoaded('tags') && $item->tags && $item->tags->isNotEmpty()) {
                $tagIds = $item->tags->pluck('id')->toArray();
                $prev = (clone $baseQuery)
                    ->whereHas('tags', function ($q) use ($tagIds) {
                        $q->whereIn('news_tags.id', $tagIds);
                    })
                    ->where(function ($q) use ($publishedAt, $item) {
                        $q->where('published_at', '<', $publishedAt)
                          ->orWhere(function ($q2) use ($publishedAt, $item) {
                              $q2->where('published_at', $publishedAt)->where('id', '<', $item->id);
                          });
                    })
                    ->orderBy('published_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
            }

            if (!$prev) {
                $prev = (clone $baseQuery)
                    ->where(function ($q) use ($publishedAt, $item) {
                        $q->where('published_at', '<', $publishedAt)
                          ->orWhere(function ($q2) use ($publishedAt, $item) {
                              $q2->where('published_at', $publishedAt)->where('id', '<', $item->id);
                          });
                    })
                    ->orderBy('published_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
            }

            // 2. Next Article Resolution (Newer than current)
            $next = (clone $baseQuery)
                ->where('news_category_id', $item->news_category_id)
                ->where(function ($q) use ($publishedAt, $item) {
                    $q->where('published_at', '>', $publishedAt)
                      ->orWhere(function ($q2) use ($publishedAt, $item) {
                          $q2->where('published_at', $publishedAt)->where('id', '>', $item->id);
                      });
                })
                ->orderBy('published_at', 'asc')
                ->orderBy('id', 'asc')
                ->first();

            if (!$next && $hasTags && $item->relationLoaded('tags') && $item->tags && $item->tags->isNotEmpty()) {
                $tagIds = $item->tags->pluck('id')->toArray();
                $next = (clone $baseQuery)
                    ->whereHas('tags', function ($q) use ($tagIds) {
                        $q->whereIn('news_tags.id', $tagIds);
                    })
                    ->where(function ($q) use ($publishedAt, $item) {
                        $q->where('published_at', '>', $publishedAt)
                          ->orWhere(function ($q2) use ($publishedAt, $item) {
                              $q2->where('published_at', $publishedAt)->where('id', '>', $item->id);
                          });
                    })
                    ->orderBy('published_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->first();
            }

            if (!$next) {
                $next = (clone $baseQuery)
                    ->where(function ($q) use ($publishedAt, $item) {
                        $q->where('published_at', '>', $publishedAt)
                          ->orWhere(function ($q2) use ($publishedAt, $item) {
                              $q2->where('published_at', $publishedAt)->where('id', '>', $item->id);
                          });
                    })
                    ->orderBy('published_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->first();
            }

            // Clean thumbnail paths for preview cards
            foreach ([$prev, $next] as $art) {
                if ($art && $art->thumbnail) {
                    $clean = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $art->thumbnail), '/');
                    $art->thumbnail = '/uploads/' . $clean;
                }
            }

            return [
                'previous' => $prev,
                'next' => $next,
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('getPrevNextArticles exception: ' . $e->getMessage());
            return ['previous' => null, 'next' => null];
        }
    }

    /**
     * Post Article Reaction (beneficial / informative) with toggle & switch logic.
     */
    public function postReaction(News $news, Request $request)
    {
        $request->validate([
            'reaction_type' => 'required|in:beneficial,informative',
        ]);

        if (!Schema::hasTable('article_reactions')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel article_reactions belum dibuat.',
            ], 422);
        }

        $newType = $request->reaction_type;

        // Visitor UUID from cookie or generate new
        $visitorUuid = $request->cookie('visitor_uuid');
        if (!$visitorUuid) {
            $visitorUuid = (string) Str::uuid();
            Cookie::queue('visitor_uuid', $visitorUuid, 525600); // 1 year
        }

        $sessionId = $request->hasSession() ? $request->session()->getId() : null;
        $ip = $request->ip();
        $agent = Str::limit($request->userAgent() ?? '', 500);

        $finalReaction = null;

        DB::transaction(function () use ($news, $visitorUuid, $sessionId, $newType, $ip, $agent, &$finalReaction) {
            $existing = ArticleReaction::where('news_id', $news->id)
                ->where('visitor_uuid', $visitorUuid)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($existing->reaction_type === $newType) {
                    // Toggle off (remove reaction)
                    $existing->delete();
                    if (Schema::hasColumn('news', 'beneficial_count') && $newType === 'beneficial' && $news->beneficial_count > 0) {
                        $news->decrement('beneficial_count');
                    } elseif (Schema::hasColumn('news', 'informative_count') && $newType === 'informative' && $news->informative_count > 0) {
                        $news->decrement('informative_count');
                    }
                    $finalReaction = null;
                } else {
                    // Switch reaction type
                    $oldType = $existing->reaction_type;
                    $existing->update([
                        'reaction_type' => $newType,
                        'session_id'    => $sessionId,
                        'ip_address'    => $ip,
                        'user_agent'    => $agent,
                    ]);

                    if (Schema::hasColumn('news', 'beneficial_count') && $oldType === 'beneficial' && $news->beneficial_count > 0) {
                        $news->decrement('beneficial_count');
                    } elseif (Schema::hasColumn('news', 'informative_count') && $oldType === 'informative' && $news->informative_count > 0) {
                        $news->decrement('informative_count');
                    }

                    if (Schema::hasColumn('news', 'beneficial_count') && $newType === 'beneficial') {
                        $news->increment('beneficial_count');
                    } elseif (Schema::hasColumn('news', 'informative_count')) {
                        $news->increment('informative_count');
                    }
                    $finalReaction = $newType;
                }
            } else {
                // New reaction
                ArticleReaction::create([
                    'news_id'       => $news->id,
                    'visitor_uuid'  => $visitorUuid,
                    'session_id'    => $sessionId,
                    'reaction_type' => $newType,
                    'ip_address'    => $ip,
                    'user_agent'    => $agent,
                ]);

                if (Schema::hasColumn('news', 'beneficial_count') && $newType === 'beneficial') {
                    $news->increment('beneficial_count');
                } elseif (Schema::hasColumn('news', 'informative_count')) {
                    $news->increment('informative_count');
                }
                $finalReaction = $newType;
            }
        });

        $news->refresh();
        $beneficial = (int) ($news->beneficial_count ?? 0);
        $informative = (int) ($news->informative_count ?? 0);
        $total = $beneficial + $informative;

        return response()->json([
            'success'            => true,
            'user_reaction'      => $finalReaction,
            'beneficial_count'   => $beneficial,
            'informative_count'  => $informative,
            'total_engagement'   => $total,
            'beneficial_percent' => $total > 0 ? round(($beneficial / $total) * 100) : 0,
            'informative_percent' => $total > 0 ? round(($informative / $total) * 100) : 0,
        ]);
    }

    /**
     * Record social share analytics asynchronously.
     */
    public function trackShare(int $id, Request $request)
    {
        $request->validate([
            'platform' => 'required|string|max:50',
        ]);

        if (!Schema::hasTable('news_shares') || !Schema::hasColumn('news', 'share_count')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {}
        }

        $item = News::findOrFail($id);
        $item->recordShare(
            $request->platform,
            $request->ip(),
            $request->header('User-Agent')
        );

        $item->refresh();

        return response()->json([
            'success' => true,
            'share_count' => $item->share_count,
        ]);
    }

    /**
     * Output dynamic 1200x630 OG Card Banner image.
     */
    public function ogBanner(string $slug)
    {
        $item = News::with('category')->where('slug', $slug)->firstOrFail();
        return NewsSeoService::renderDynamicOgBanner($item);
    }
}
