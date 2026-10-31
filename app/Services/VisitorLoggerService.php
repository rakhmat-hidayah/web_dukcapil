<?php

namespace App\Services;

use App\Jobs\ProcessVisitorGeolocationJob;
use App\Models\VisitorGeolocation;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class VisitorLoggerService
{
    /**
     * Log incoming visitor request (< 5ms target additional time).
     */
    public static function log(Request $request): ?VisitorLog
    {
        try {
            // 1. Get or generate Visitor UUID (Cookie-based for 1 year)
            $visitorUuid = $request->cookie('visitor_uuid');
            if (!$visitorUuid) {
                $visitorUuid = (string) Str::uuid();
                Cookie::queue('visitor_uuid', $visitorUuid, 525600); // 1 year cookie
            }

            // 2. Extract Session ID & Request info
            $sessionId = $request->hasSession() ? $request->session()->getId() : null;
            $ipAddress = $request->ip() ?? '127.0.0.1';
            $url = Str::limit($request->fullUrl(), 500);
            $referer = Str::limit($request->headers->get('referer') ?? '', 500);
            $method = $request->method();
            $userAgent = Str::limit($request->userAgent() ?? '', 500);

            // 3. Parse User Agent & Analyze Traffic Intelligence
            $agentData = AgentParser::parse($userAgent);
            $intelligence = VisitorIntelligenceService::analyze($request, $agentData);

            // 4. Record to visitor_logs table
            $visitorLog = VisitorLog::create([
                'visitor_uuid'             => $visitorUuid,
                'session_id'               => $sessionId,
                'ip_address'               => $ipAddress,
                'url'                      => $url,
                'method'                   => $method,
                'user_agent'               => $userAgent,
                'browser'                  => $agentData['browser'],
                'browser_version'          => $agentData['browser_version'],
                'operating_system'         => $agentData['operating_system'],
                'operating_system_version' => $agentData['operating_system_version'],
                'device_type'              => $agentData['device_type'],
                'device'                   => $agentData['device'],
                'platform'                 => $agentData['operating_system'],
                'is_robot'                 => ! $intelligence['is_human'],
                'is_human'                 => $intelligence['is_human'],
                'visitor_category'         => $intelligence['visitor_category'],
                'bot_name'                 => $intelligence['bot_name'],
                'detection_reason'         => $intelligence['detection_reason'],
                'referrer'                 => $referer,
                'visited_at'               => now(),
            ]);

            // 5. Create initial Pending Geolocation record
            VisitorGeolocation::create([
                'visitor_log_id' => $visitorLog->id,
                'status'         => 'pending',
            ]);

            // 6. Dispatch background Geolocation Queue Job (Non-blocking)
            ProcessVisitorGeolocationJob::dispatch($visitorLog->id, $ipAddress);

            return $visitorLog;

        } catch (\Throwable $e) {
            // Fail silently to ensure request is never blocked by logger error
            \Log::error('VisitorLoggerService Error: ' . $e->getMessage());
            return null;
        }
    }
}
