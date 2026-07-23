<?php

namespace App\Services;

class AgentParser
{
    /**
     * Parse User-Agent string to extract browser, platform, and device.
     */
    public static function parse(string $userAgent): array
    {
        $browser = self::getBrowser($userAgent);
        $platform = self::getPlatform($userAgent);
        $device = self::getDevice($userAgent);

        return [
            'browser' => $browser,
            'platform' => $platform,
            'device' => $device,
        ];
    }

    private static function getBrowser(string $ua): string
    {
        if (preg_match('/msie/i', $ua) && !preg_match('/opera/i', $ua)) return 'Internet Explorer';
        if (preg_match('/trident/i', $ua)) return 'Internet Explorer';
        if (preg_match('/edg/i', $ua)) return 'Edge';
        if (preg_match('/firefox/i', $ua)) return 'Firefox';
        if (preg_match('/chrome/i', $ua)) return 'Chrome';
        if (preg_match('/opera/i', $ua) || preg_match('/opr/i', $ua)) return 'Opera';
        if (preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua)) return 'Safari';
        
        return 'Unknown';
    }

    private static function getPlatform(string $ua): string
    {
        if (preg_match('/windows|win32/i', $ua)) return 'Windows';
        if (preg_match('/android/i', $ua)) return 'Android';
        if (preg_match('/iphone|ipad|ipod/i', $ua)) return 'iOS';
        if (preg_match('/macintosh|mac os x/i', $ua)) return 'macOS';
        if (preg_match('/linux/i', $ua)) return 'Linux';
        
        return 'Unknown';
    }

    private static function getDevice(string $ua): string
    {
        if (preg_match('/tablet|ipad/i', $ua)) return 'Tablet';
        if (preg_match('/mobile|iphone|ipod|android/i', $ua)) {
            // Android tablet can sometimes not contain "mobile" but contains "android".
            // So we check tablet first.
            return 'Mobile';
        }
        
        return 'Desktop';
    }
}
