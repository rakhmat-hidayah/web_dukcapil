<?php

namespace App\Services\Applicant;

use Illuminate\Http\Request;

class ApplicantDeviceDetector
{
    public const CATEGORY_SMARTPHONE  = 'smartphone';
    public const CATEGORY_TABLET      = 'tablet';
    public const CATEGORY_DESKTOP     = 'desktop';
    public const CATEGORY_BOT_UNKNOWN = 'bot_unknown';

    /**
     * Inspect incoming request (User-Agent and HTTP Client Hints)
     * and classify into device category for applicant security policy.
     *
     * @return array{
     *     is_mobile: bool,
     *     category: string,
     *     device_label: string,
     *     rejection_reason: ?string
     * }
     */
    public static function detect(Request $request): array
    {
        $rawUa = trim((string) ($request->userAgent()
            ?: $request->header('User-Agent')
            ?: $request->header('X-User-Agent')
            ?: $request->header('X-Original-User-Agent')
            ?: $request->server('HTTP_USER_AGENT')
            ?: ''));

        // Client Hints headers (RFC 8942 / Chromium standard)
        $chMobile = self::extractHeader($request, 'sec-ch-ua-mobile');
        $chPlatform = trim((string) (self::extractHeader($request, 'sec-ch-ua-platform') ?? ''), ' "');
        $chUa = self::extractHeader($request, 'sec-ch-ua');

        // 1. Fail-closed: Empty User-Agent and no Client Hints
        if (empty($rawUa) && empty($chMobile) && empty($chPlatform)) {
            return [
                'is_mobile'        => false,
                'category'         => self::CATEGORY_BOT_UNKNOWN,
                'device_label'     => 'Perangkat Tidak Dikenal / Tanpa User-Agent',
                'rejection_reason' => 'Header User-Agent kosong atau tidak valid.',
            ];
        }

        // 2. Automated Tools, Bots, Crawlers, and Headless Drivers
        if (self::isBotOrAutomated($rawUa)) {
            return [
                'is_mobile'        => false,
                'category'         => self::CATEGORY_BOT_UNKNOWN,
                'device_label'     => 'Automated Client / Script / Bot',
                'rejection_reason' => 'Permintaan terdeteksi berasal dari alat otomatisasi atau bot.',
            ];
        }

        // 3. Tablet Detection (Explicit tablet keywords or iPad / Android without Mobile)
        if (self::isTabletDevice($rawUa, $chMobile)) {
            return [
                'is_mobile'        => false,
                'category'         => self::CATEGORY_TABLET,
                'device_label'     => 'Perangkat Tablet (iPad / Android Tablet)',
                'rejection_reason' => 'Perangkat terdeteksi sebagai Tablet. Login pemohon hanya diperbolehkan melalui smartphone.',
            ];
        }

        // 4. Smartphone Detection
        // Sinyal A: Client Hints Mobile = ?1 (Signal resmi browser mobile Chromium)
        $isChMobile = ($chMobile === '?1' || $chMobile === '1' || $chMobile === 'true');

        // Sinyal B: Android Smartphone (mengandung 'Android' DAN 'Mobile')
        $isAndroidPhone = preg_match('/Android/i', $rawUa) && (preg_match('/Mobile/i', $rawUa) || $isChMobile);

        // Sinyal C: iPhone / iPod
        $isIPhone = preg_match('/iPhone|iPod/i', $rawUa);

        // Sinyal D: Mobile browser lainnya (Opera Mini, BlackBerry, Windows Phone, etc)
        $isOtherMobile = preg_match('/Mobile|Windows Phone|BlackBerry|Opera Mini|IEMobile|Mobile Safari/i', $rawUa);

        if ($isChMobile || $isAndroidPhone || $isIPhone || $isOtherMobile) {
            $label = self::resolveDeviceLabel($rawUa, $chPlatform);
            return [
                'is_mobile'        => true,
                'category'         => self::CATEGORY_SMARTPHONE,
                'device_label'     => $label,
                'rejection_reason' => null,
            ];
        }

        // 5. Desktop / Laptop Detection (Windows, macOS, Linux, ChromeOS)
        $desktopLabel = self::resolveDesktopLabel($rawUa, $chPlatform);
        return [
            'is_mobile'        => false,
            'category'         => self::CATEGORY_DESKTOP,
            'device_label'     => $desktopLabel,
            'rejection_reason' => 'Perangkat terdeteksi sebagai Komputer PC / Laptop.',
        ];
    }

    /**
     * Check if request is confirmed as a mobile smartphone.
     */
    public static function isMobileSmartphone(Request $request): bool
    {
        return self::detect($request)['is_mobile'];
    }

    /**
     * Get the device category string.
     */
    public static function getCategory(Request $request): string
    {
        return self::detect($request)['category'];
    }

    /**
     * Check if User-Agent indicates a tablet device.
     */
    private static function isTabletDevice(string $ua, ?string $chMobile): bool
    {
        // Explicit tablet keywords
        if (preg_match('/ipad|tablet|playbook|silk|kindle/i', $ua)) {
            return true;
        }

        // Android without Mobile keyword and Client Hint says non-mobile (?0)
        if (preg_match('/Android/i', $ua) && !preg_match('/Mobile/i', $ua) && $chMobile !== '?1') {
            return true;
        }

        return false;
    }

    /**
     * Check if User-Agent matches known bots, automated tools, or headless browsers.
     */
    private static function isBotOrAutomated(string $ua): bool
    {
        $botPattern = '/bot|crawl|slurp|spider|mediapartners|facebookexternalhit|twitterbot|lighthouse|googlebot|bingbot|yandexbot|duckduckbot|baiduspider|curl|postman|python|guzzle|wget|uptime|monitor|ping|probe|check|health|scanner|headless|phantom|selenium|puppeteer|playwright|httpclient|java|apache|go-http|okhttp|axios/i';
        return (bool) preg_match($botPattern, $ua);
    }

    /**
     * Resolve user-friendly label for mobile smartphone.
     */
    private static function resolveDeviceLabel(string $ua, string $chPlatform): string
    {
        $os = 'Smartphone';
        if (preg_match('/Android/i', $ua) || stripos($chPlatform, 'Android') !== false) {
            $os = 'Smartphone Android';
        } elseif (preg_match('/iPhone|iPod|iOS/i', $ua) || stripos($chPlatform, 'iOS') !== false) {
            $os = 'Apple iPhone';
        }

        $browser = 'Browser Mobile';
        if (preg_match('/SamsungBrowser/i', $ua)) {
            $browser = 'Samsung Internet';
        } elseif (preg_match('/MiuiBrowser/i', $ua)) {
            $browser = 'Miui Browser';
        } elseif (preg_match('/Chrome|CriOS/i', $ua)) {
            $browser = 'Google Chrome Mobile';
        } elseif (preg_match('/Safari/i', $ua) && !preg_match('/Chrome|CriOS/i', $ua)) {
            $browser = 'Apple Safari Mobile';
        } elseif (preg_match('/Firefox|FxiOS/i', $ua)) {
            $browser = 'Mozilla Firefox Mobile';
        } elseif (preg_match('/Opera|OPR|OPT/i', $ua)) {
            $browser = 'Opera Mobile';
        }

        return "{$browser} ({$os})";
    }

    /**
     * Resolve user-friendly label for desktop PC/laptop.
     */
    private static function resolveDesktopLabel(string $ua, string $chPlatform): string
    {
        $os = 'Desktop / Laptop';
        if (preg_match('/Windows NT 10/i', $ua) || stripos($chPlatform, 'Windows') !== false) {
            $os = 'Windows PC';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $ua) || stripos($chPlatform, 'macOS') !== false) {
            $os = 'Apple Mac';
        } elseif (preg_match('/Linux|Ubuntu|X11/i', $ua) || stripos($chPlatform, 'Linux') !== false) {
            $os = 'Linux PC';
        } elseif (preg_match('/CrOS/i', $ua)) {
            $os = 'ChromeOS';
        }

        $browser = 'Desktop Browser';
        if (preg_match('/Edg/i', $ua)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/Chrome/i', $ua)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/Safari/i', $ua) && !preg_match('/Chrome/i', $ua)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/Firefox/i', $ua)) {
            $browser = 'Mozilla Firefox';
        }

        return "{$browser} ({$os})";
    }

    /**
     * Safely extract header value case-insensitively.
     */
    private static function extractHeader(Request $request, string $key): ?string
    {
        $val = $request->header($key);
        if ($val !== null && $val !== '') {
            return is_array($val) ? ($val[0] ?? null) : (string) $val;
        }

        // Check server variables as fallback (e.g., HTTP_SEC_CH_UA_MOBILE)
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        $serverVal = $request->server($serverKey);
        if ($serverVal !== null && $serverVal !== '') {
            return (string) $serverVal;
        }

        return null;
    }
}
