<?php

namespace App\Services;

class AgentParser
{
    /**
     * Parse User-Agent string to extract detailed browser, OS, device info and bot detection.
     */
    public static function parse(string $userAgent): array
    {
        $ua = trim($userAgent);

        if (empty($ua)) {
            return [
                'browser'                  => 'Direct / Unknown',
                'browser_version'          => null,
                'operating_system'         => 'Unknown OS',
                'operating_system_version' => null,
                'device_type'              => 'Desktop',
                'device'                   => 'Desktop',
                'platform'                 => 'Unknown OS',
                'is_robot'                 => false,
            ];
        }

        $isRobot = self::isRobot($ua);
        $browser = self::getBrowserInfo($ua);
        $os = self::getOsInfo($ua);
        $device = self::getDeviceType($ua, $isRobot);

        return [
            'browser'                  => $browser['name'],
            'browser_version'          => $browser['version'],
            'operating_system'         => $os['name'],
            'operating_system_version' => $os['version'],
            'device_type'              => $device,
            'device'                   => $device,
            'platform'                 => $os['name'],
            'is_robot'                 => $isRobot,
        ];
    }

    /**
     * Get browser name and version.
     */
    private static function getBrowserInfo(string $ua): array
    {
        $name = 'Browser Direct / Unknown';
        $version = null;

        // Automated Bots / Dev tools
        if (preg_match('/curl\/([0-9\.]+)/i', $ua, $m)) {
            return ['name' => 'cURL Tool', 'version' => $m[1]];
        }
        if (preg_match('/PostmanRuntime\/([0-9\.]+)/i', $ua, $m)) {
            return ['name' => 'Postman Client', 'version' => $m[1]];
        }
        if (preg_match('/GuzzleHttp\/([0-9\.]+)/i', $ua, $m)) {
            return ['name' => 'Guzzle HTTP', 'version' => $m[1]];
        }
        if (preg_match('/Python-urllib\/([0-9\.]+)/i', $ua, $m) || preg_match('/python-requests\/([0-9\.]+)/i', $ua, $m)) {
            return ['name' => 'Python Script', 'version' => $m[1]];
        }
        if (preg_match('/Go-http-client\/([0-9\.]+)/i', $ua, $m)) {
            return ['name' => 'Go HTTP Client', 'version' => $m[1]];
        }

        // Webview & Social Media In-App Browsers
        if (preg_match('/FBAN|FBAV/i', $ua)) {
            return ['name' => 'Facebook App', 'version' => null];
        }
        if (preg_match('/Instagram/i', $ua)) {
            return ['name' => 'Instagram App', 'version' => null];
        }
        if (preg_match('/TikTok/i', $ua)) {
            return ['name' => 'TikTok App', 'version' => null];
        }

        // Mobile Browsers
        if (preg_match('/SamsungBrowser\/([0-9\.]+)/i', $ua, $m)) {
            return ['name' => 'Samsung Internet', 'version' => $m[1]];
        }
        if (preg_match('/UCBrowser\/([0-9\.]+)/i', $ua, $m)) {
            return ['name' => 'UC Browser', 'version' => $m[1]];
        }

        // Major Desktop / Mobile Browsers
        if (preg_match('/(Edg|Edge)\/([0-9\.]+)/i', $ua, $m)) {
            $name = 'Edge';
            $version = $m[2];
        } elseif (preg_match('/(OPR|Opera)\/([0-9\.]+)/i', $ua, $m)) {
            $name = 'Opera';
            $version = $m[2];
        } elseif (preg_match('/(Chrome|CriOS)\/([0-9\.]+)/i', $ua, $m)) {
            $name = 'Chrome';
            $version = $m[2];
        } elseif (preg_match('/(Firefox|FxiOS)\/([0-9\.]+)/i', $ua, $m)) {
            $name = 'Firefox';
            $version = $m[2];
        } elseif (preg_match('/Version\/([0-9\.]+).*Safari/i', $ua, $m)) {
            $name = 'Safari';
            $version = $m[1];
        } elseif (preg_match('/Safari\/([0-9\.]+)/i', $ua, $m)) {
            $name = 'Safari';
            $version = $m[1];
        } elseif (preg_match('/MSIE ([0-9\.]+)/i', $ua, $m) || preg_match('/rv:([0-9\.]+).*Trident/i', $ua, $m)) {
            $name = 'Internet Explorer';
            $version = $m[1];
        }

        return ['name' => $name, 'version' => $version];
    }

    /**
     * Get Operating System name and version.
     */
    private static function getOsInfo(string $ua): array
    {
        $name = 'Unknown OS';
        $version = null;

        if (preg_match('/Windows NT ([0-9\.]+)/i', $ua, $m)) {
            $name = 'Windows';
            $winVersions = [
                '10.0' => '(10 / 11)',
                '6.3'  => '8.1',
                '6.2'  => '8',
                '6.1'  => '7',
                '6.0'  => 'Vista',
                '5.1'  => 'XP',
            ];
            $version = $winVersions[$m[1]] ?? $m[1];
        } elseif (preg_match('/Android ([0-9\.]+)/i', $ua, $m)) {
            $name = 'Android';
            $version = $m[1];
        } elseif (preg_match('/iPhone OS ([0-9_]+)/i', $ua, $m) || preg_match('/CPU OS ([0-9_]+)/i', $ua, $m)) {
            $name = 'iOS';
            $version = str_replace('_', '.', $m[1]);
        } elseif (preg_match('/Mac OS X ([0-9_\.]+)/i', $ua, $m)) {
            $name = 'macOS';
            $version = str_replace('_', '.', $m[1]);
        } elseif (preg_match('/CrOS/i', $ua)) {
            $name = 'ChromeOS';
        } elseif (preg_match('/Ubuntu/i', $ua)) {
            $name = 'Ubuntu Linux';
        } elseif (preg_match('/Linux/i', $ua)) {
            $name = 'Linux';
        }

        return ['name' => $name, 'version' => $version];
    }

    /**
     * Detect if user agent is a search engine bot / crawler.
     */
    private static function isRobot(string $ua): bool
    {
        $robotsPattern = '/bot|crawl|slurp|spider|mediapartners|facebookexternalhit|twitterbot|lighthouse|googlebot|bingbot|yandexbot|duckduckbot|baiduspider|curl|postman|python|guzzle|wget/i';
        return (bool) preg_match($robotsPattern, $ua);
    }

    /**
     * Get device category: Robot, Tablet, Mobile, or Desktop.
     */
    private static function getDeviceType(string $ua, bool $isRobot): string
    {
        if ($isRobot) {
            return 'Robot';
        }
        if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) {
            return 'Tablet';
        }
        if (preg_match('/mobile|iphone|ipod|android|blackberry|opera mini|windows phone/i', $ua)) {
            return 'Mobile';
        }
        return 'Desktop';
    }
}
