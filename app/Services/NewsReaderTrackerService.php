<?php

namespace App\Services;

use App\Models\ArticleReaderLog;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsReaderTrackerService
{
    /**
     * Record article read analytics with bot filtering, TTL windowing, and atomic unique counting.
     */
    public static function recordRead(News $news, ?Request $request = null): array
    {
        $request = $request ?? request();
        if (!$news->id) {
            return ['is_human' => false, 'counted_as_unique' => false];
        }

        try {
            // 1. Get or generate Visitor UUID (Cookie-based for 1 year)
            $visitorUuid = $request->cookie('visitor_uuid');
            if (!$visitorUuid) {
                $visitorUuid = (string) Str::uuid();
                Cookie::queue('visitor_uuid', $visitorUuid, 525600); // 1 year cookie
            }

            // 2. Extract Request Info & Analyze Traffic Intelligence
            $userAgent = Str::limit($request->userAgent() ?? '', 500);
            $ipAddress = $request->ip() ?? '127.0.0.1';
            $sessionId = $request->hasSession() ? $request->session()->getId() : null;

            $agentData = AgentParser::parse($userAgent);
            $intelligence = VisitorIntelligenceService::analyze($request, $agentData);
            $isHuman = (bool) ($intelligence['is_human'] ?? true);

            // Configurable TTL window in hours (default: 24 hours)
            $ttlHours = (int) config('app.news_reader_ttl_hours', 24);
            $now = now();

            // 3. Handle Bot / Robot Hits
            if (!$isHuman) {
                DB::transaction(function () use ($news, $visitorUuid, $sessionId, $ipAddress, $userAgent, $now) {
                    $log = ArticleReaderLog::where('news_id', $news->id)
                        ->where('visitor_uuid', $visitorUuid)
                        ->where('is_robot', true)
                        ->first();

                    if ($log) {
                        $log->update([
                            'last_read_at' => $now,
                            'read_count'   => DB::raw('read_count + 1'),
                        ]);
                    } else {
                        ArticleReaderLog::create([
                            'news_id'       => $news->id,
                            'visitor_uuid'  => $visitorUuid,
                            'session_id'    => $sessionId,
                            'ip_address'    => $ipAddress,
                            'user_agent'    => $userAgent,
                            'is_robot'      => true,
                            'first_read_at' => $now,
                            'last_read_at'  => $now,
                            'read_count'    => 1,
                        ]);
                    }

                    $news->increment('robot_hits');
                });

                return [
                    'is_human' => false,
                    'counted_as_unique' => false,
                ];
            }

            // 4. Handle Human Readers with Atomic Lock Transaction
            $countedAsUnique = false;

            DB::transaction(function () use ($news, $visitorUuid, $sessionId, $ipAddress, $userAgent, $now, $ttlHours, &$countedAsUnique) {
                // Lock log row for update to prevent race conditions during high concurrency
                $log = ArticleReaderLog::where('news_id', $news->id)
                    ->where('visitor_uuid', $visitorUuid)
                    ->where('is_robot', false)
                    ->lockForUpdate()
                    ->first();

                if (!$log) {
                    // Case A: First time reader ever for this article
                    ArticleReaderLog::create([
                        'news_id'       => $news->id,
                        'visitor_uuid'  => $visitorUuid,
                        'session_id'    => $sessionId,
                        'ip_address'    => $ipAddress,
                        'user_agent'    => $userAgent,
                        'is_robot'      => false,
                        'first_read_at' => $now,
                        'last_read_at'  => $now,
                        'read_count'    => 1,
                    ]);

                    $news->increment('unique_readers');
                    $news->increment('page_views');
                    $news->increment('view_count');
                    $countedAsUnique = true;

                } else {
                    // Case B: Existing reader - check if last read is older than TTL window
                    $lastRead = $log->last_read_at;
                    $isExpiredTtl = $lastRead ? $lastRead->diffInHours($now) >= $ttlHours : true;

                    $log->update([
                        'last_read_at' => $now,
                        'read_count'   => DB::raw('read_count + 1'),
                        'session_id'   => $sessionId ?? $log->session_id,
                        'ip_address'   => $ipAddress ?? $log->ip_address,
                    ]);

                    if ($isExpiredTtl) {
                        $news->increment('unique_readers');
                        $countedAsUnique = true;
                    }

                    $news->increment('page_views');
                    $news->increment('view_count');
                }
            });

            return [
                'is_human'          => true,
                'counted_as_unique' => $countedAsUnique,
            ];

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("NewsReaderTrackerService error for news {$news->id}: " . $e->getMessage());
            return ['is_human' => true, 'counted_as_unique' => false];
        }
    }
}
