<?php

namespace App\Services;

use Illuminate\Http\Request;

class VisitorIntelligenceService
{
    /**
     * Classify visitor request into Intelligence Categories:
     * - human
     * - search_engine_bot
     * - social_media_bot
     * - monitoring_bot
     * - ai_crawler
     * - seo_bot
     * - security_scanner
     * - unknown_bot
     */
    public static function analyze(Request $request, array $agentData = []): array
    {
        $path = strtolower(ltrim($request->path(), '/'));
        $fullUrl = strtolower($request->fullUrl());
        $ua = trim($request->userAgent() ?? '');

        // 1. Endpoint & Path Probing Detection (Security Scanners & Special File Endpoints)
        $endpointResult = self::checkEndpointSecurity($path, $fullUrl);
        if ($endpointResult !== null) {
            return $endpointResult;
        }

        // 2. User-Agent Pattern Classification
        $uaResult = self::classifyUserAgent($ua);
        if ($uaResult !== null) {
            return $uaResult;
        }

        // 3. Missing User-Agent check
        if (empty($ua)) {
            return [
                'is_human'         => false,
                'visitor_category' => 'unknown_bot',
                'bot_name'         => 'Empty User-Agent',
                'detection_reason' => 'Missing User-Agent Header',
            ];
        }

        // 4. Agent Parser Robot fallback
        if (!empty($agentData['is_robot'])) {
            return [
                'is_human'         => false,
                'visitor_category' => 'unknown_bot',
                'bot_name'         => $agentData['browser'] ?? 'Automated Script',
                'detection_reason' => 'Generic Robot User Agent Pattern',
            ];
        }

        // 5. Default: Legitimate Human Visitor
        return [
            'is_human'         => true,
            'visitor_category' => 'human',
            'bot_name'         => null,
            'detection_reason' => 'Legitimate Human Session',
        ];
    }

    /**
     * Check path/endpoint for known security scanners, probes, or bot targets.
     */
    private static function checkEndpointSecurity(string $path, string $fullUrl): ?array
    {
        // Search Engine & System File Endpoints
        if ($path === 'robots.txt') {
            return [
                'is_human'         => false,
                'visitor_category' => 'search_engine_bot',
                'bot_name'         => 'Robots.txt Crawler',
                'detection_reason' => 'Matched Endpoint: /robots.txt',
            ];
        }

        if ($path === 'sitemap.xml' || str_contains($path, 'sitemap')) {
            return [
                'is_human'         => false,
                'visitor_category' => 'search_engine_bot',
                'bot_name'         => 'Sitemap Crawler',
                'detection_reason' => 'Matched Endpoint: /sitemap.xml',
            ];
        }

        // WordPress Vulnerability Probes
        if (
            str_contains($path, 'wp-admin') ||
            str_contains($path, 'wp-login.php') ||
            str_contains($path, 'wp-content') ||
            str_contains($path, 'wp-includes') ||
            str_contains($path, 'xmlrpc.php') ||
            str_contains($fullUrl, 'rest_route=')
        ) {
            return [
                'is_human'         => false,
                'visitor_category' => 'security_scanner',
                'bot_name'         => 'WordPress Scanner',
                'detection_reason' => 'Security Scan Pattern: WordPress Vulnerability Probe (' . $path . ')',
            ];
        }

        // Sensitive Files & Configuration Probes
        $sensitivePatterns = [
            '.env', '.git', '.aws', 'config.php', 'setup.php', 'eval-stdin.php',
            'phpmyadmin', 'vendor/', 'composer.json', 'composer.lock', 'storage/logs',
            '.htaccess', 'web.config', 'admin/config', 'db.php', 'database.yml'
        ];

        foreach ($sensitivePatterns as $pattern) {
            if (str_contains($path, strtolower($pattern)) || str_contains($fullUrl, strtolower($pattern))) {
                return [
                    'is_human'         => false,
                    'visitor_category' => 'security_scanner',
                    'bot_name'         => 'Security Scanner',
                    'detection_reason' => 'Security Scan Pattern: Sensitive File Probe (' . $pattern . ')',
                ];
            }
        }

        return null;
    }

    /**
     * Classify User-Agent string against known bot categories.
     */
    private static function classifyUserAgent(string $ua): ?array
    {
        if (empty($ua)) {
            return null;
        }

        // A. Search Engine Bots
        $searchEngines = [
            'Googlebot'       => 'Googlebot',
            'Bingbot'         => 'Bingbot',
            'YandexBot'       => 'YandexBot',
            'Baiduspider'     => 'Baiduspider',
            'DuckDuckBot'     => 'DuckDuckBot',
            'Yahoo! Slurp'    => 'Yahoo! Slurp',
            'Sogou'           => 'Sogou Spider',
            'Exabot'          => 'Exabot',
            'Qwantify'        => 'Qwantify',
            'mediapartners'   => 'Google AdSense Bot',
        ];

        foreach ($searchEngines as $pattern => $name) {
            if (stripos($ua, $pattern) !== false) {
                return [
                    'is_human'         => false,
                    'visitor_category' => 'search_engine_bot',
                    'bot_name'         => 'Known ' . $name,
                    'detection_reason' => 'Matched User Agent: ' . $name,
                ];
            }
        }

        // B. Social Media Preview Bots
        $socialBots = [
            'facebookexternalhit' => 'Facebook Preview',
            'Twitterbot'          => 'Twitterbot',
            'LinkedInBot'         => 'LinkedInBot',
            'WhatsApp'            => 'WhatsApp Preview',
            'TelegramBot'         => 'TelegramBot',
            'Pinterestbot'        => 'Pinterestbot',
            'Discordbot'          => 'Discordbot',
            'Slackbot'            => 'Slackbot',
            'SkypeUriPreview'     => 'Skype Preview',
        ];

        foreach ($socialBots as $pattern => $name) {
            if (stripos($ua, $pattern) !== false) {
                return [
                    'is_human'         => false,
                    'visitor_category' => 'social_media_bot',
                    'bot_name'         => $name,
                    'detection_reason' => 'Matched User Agent: ' . $name,
                ];
            }
        }

        // C. AI Crawlers
        $aiBots = [
            'GPTBot'          => 'OpenAI GPTBot',
            'ChatGPT-User'    => 'ChatGPT User',
            'ClaudeBot'       => 'Anthropic ClaudeBot',
            'Claude-Web'      => 'Anthropic Claude Web',
            'Bytespider'      => 'ByteDance Bytespider',
            'PerplexityBot'   => 'Perplexity AI Bot',
            'CCBot'           => 'Common Crawl CCBot',
            'Anthropic-AI'    => 'Anthropic AI',
            'Cohere-ai'       => 'Cohere AI',
            'Omgilibot'       => 'Omgili AI Crawler',
        ];

        foreach ($aiBots as $pattern => $name) {
            if (stripos($ua, $pattern) !== false) {
                return [
                    'is_human'         => false,
                    'visitor_category' => 'ai_crawler',
                    'bot_name'         => $name,
                    'detection_reason' => 'Matched User Agent: ' . $name,
                ];
            }
        }

        // D. SEO & Analytical Bots
        $seoBots = [
            'AhrefsBot'       => 'AhrefsBot',
            'SemrushBot'      => 'SemrushBot',
            'MJ12bot'         => 'Majestic MJ12bot',
            'DotBot'          => 'Moz DotBot',
            'SerpstatBot'     => 'SerpstatBot',
            'ROGERBOT'        => 'Moz Rogerbot',
            'DataForSeoBot'   => 'DataForSEO Bot',
        ];

        foreach ($seoBots as $pattern => $name) {
            if (stripos($ua, $pattern) !== false) {
                return [
                    'is_human'         => false,
                    'visitor_category' => 'seo_bot',
                    'bot_name'         => $name,
                    'detection_reason' => 'Matched User Agent: ' . $name,
                ];
            }
        }

        // E. Monitoring & Uptime Bots
        $monitoringBots = [
            'UptimeRobot'     => 'UptimeRobot',
            'Pingdom'         => 'Pingdom Monitoring',
            'StatusCake'      => 'StatusCake',
            'Datadog'         => 'Datadog Agent',
            'NewRelicPinger'  => 'NewRelic Synthetics',
            'BetterUptime'    => 'Better Uptime Bot',
            'HetrixTools'     => 'HetrixTools Uptime',
            'Site24x7'        => 'Site24x7 Monitoring',
        ];

        foreach ($monitoringBots as $pattern => $name) {
            if (stripos($ua, $pattern) !== false) {
                return [
                    'is_human'         => false,
                    'visitor_category' => 'monitoring_bot',
                    'bot_name'         => $name,
                    'detection_reason' => 'Matched User Agent: ' . $name,
                ];
            }
        }

        // F. Security Scanners
        $securityScanners = [
            'zgrab'           => 'Zgrab Scanner',
            'nmap'            => 'Nmap Network Scanner',
            'nikto'           => 'Nikto Web Scanner',
            'nessus'          => 'Nessus Security Scanner',
            'sqlmap'          => 'SQLmap Exploit Tool',
            'acunetix'        => 'Acunetix Vulnerability Scanner',
            'censysinspect'   => 'Censys Inspector',
            'shodan'          => 'Shodan Security Scanner',
            'WPScan'          => 'WP-Scan WordPress Scanner',
            'nuclei'          => 'Nuclei Vulnerability Scanner',
            'openvas'         => 'OpenVAS Security Scanner',
            'masscan'         => 'Masscan Port Scanner',
            'dirbuster'       => 'DirBuster Directory Probe',
        ];

        foreach ($securityScanners as $pattern => $name) {
            if (stripos($ua, $pattern) !== false) {
                return [
                    'is_human'         => false,
                    'visitor_category' => 'security_scanner',
                    'bot_name'         => $name,
                    'detection_reason' => 'Security Scan Pattern: ' . $name,
                ];
            }
        }

        // G. Automated Scripts & Command Line Tools (Unknown Bots)
        $scriptTools = [
            'curl/'           => 'cURL Tool',
            'Wget/'           => 'Wget Utility',
            'python-requests' => 'Python Requests Script',
            'Python-urllib'   => 'Python urllib Script',
            'GuzzleHttp'      => 'Guzzle HTTP Client',
            'PostmanRuntime'  => 'Postman API Client',
            'Go-http-client'  => 'Go HTTP Client',
            'Apache-HttpClient' => 'Java Apache HttpClient',
            'okhttp'          => 'OkHttp Client',
        ];

        foreach ($scriptTools as $pattern => $name) {
            if (stripos($ua, $pattern) !== false) {
                return [
                    'is_human'         => false,
                    'visitor_category' => 'unknown_bot',
                    'bot_name'         => $name,
                    'detection_reason' => 'Matched User Agent: Automated Script (' . $name . ')',
                ];
            }
        }

        // H. Generic bot keyword fallback
        if (preg_match('/bot|crawl|slurp|spider|lighthouse/i', $ua)) {
            return [
                'is_human'         => false,
                'visitor_category' => 'unknown_bot',
                'bot_name'         => 'Generic Robot / Crawler',
                'detection_reason' => 'Matched User Agent: Generic Robot Pattern',
            ];
        }

        return null;
    }
}
