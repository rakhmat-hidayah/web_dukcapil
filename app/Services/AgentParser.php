<?php

namespace App\Services;

class AgentParser
{
    /**
     * Parse User-Agent string and optional request headers (Client Hints)
     * to extract detailed browser, OS, device info, and bot detection.
     */
    public static function parse(string $userAgent, array $headers = []): array
    {
        $ua = trim($userAgent);

        // Normalize Client Hints headers if provided
        $chUa              = self::extractHeader($headers, 'sec-ch-ua');
        $chPlatform        = trim(self::extractHeader($headers, 'sec-ch-ua-platform') ?? '', ' "');
        $chPlatformVersion = trim(self::extractHeader($headers, 'sec-ch-ua-platform-version') ?? '', ' "');
        $chMobile          = self::extractHeader($headers, 'sec-ch-ua-mobile');
        $chModel           = trim(self::extractHeader($headers, 'sec-ch-ua-model') ?? '', ' "');

        // Completely empty UA and no Client Hints => Robot / Script
        if (empty($ua) && empty($chUa) && empty($chPlatform)) {
            return [
                'browser'                  => 'Direct / Unknown',
                'browser_version'          => null,
                'operating_system'         => 'Unknown OS',
                'operating_system_version' => null,
                'device_type'              => 'Robot',
                'device'                   => 'Robot',
                'platform'                 => 'Unknown OS',
                'is_robot'                 => true,
            ];
        }

        $isRobot = self::isRobot($ua);
        $browser = self::getBrowserInfo($ua, $chUa);
        $os      = self::getOsInfo($ua, $chPlatform, $chPlatformVersion);

        // Client Hints platform override if UA was generic/Linux but Client Hints says Android/Windows
        if (($os['name'] === 'Unknown OS' || $os['name'] === 'Linux') && !empty($chPlatform)) {
            if (stripos($chPlatform, 'Android') !== false) {
                $os['name'] = 'Android';
                if (!empty($chPlatformVersion)) {
                    $os['version'] = $chPlatformVersion;
                }
            } elseif (stripos($chPlatform, 'Windows') !== false) {
                $os['name'] = 'Windows';
            } elseif (stripos($chPlatform, 'iOS') !== false) {
                $os['name'] = 'iOS';
            } elseif (stripos($chPlatform, 'macOS') !== false) {
                $os['name'] = 'macOS';
            }
        }

        // Client Hints Chrome override if UA was generic
        if ($browser['name'] === 'Browser Direct / Unknown' && !empty($chUa)) {
            if (stripos($chUa, 'Google Chrome') !== false || stripos($chUa, 'Chrome') !== false) {
                $browser['name'] = 'Chrome';
            } elseif (stripos($chUa, 'Chromium') !== false) {
                $browser['name'] = 'Chromium';
            }
        }

        // Determine Device Type accurately (Mobile, Tablet, Desktop, Robot)
        $device = self::getDeviceType($ua, $isRobot, $os['name'], $chMobile);

        // If UA lacks any recognizable browser AND operating system, and no Client Hints, classify as script
        if ($browser['name'] === 'Browser Direct / Unknown' && $os['name'] === 'Unknown OS' && empty($chUa)) {
            $isRobot = true;
            $device = 'Robot';
        }

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
     * Safely extract a header value case-insensitively from array.
     */
    private static function extractHeader(array $headers, string $key): ?string
    {
        $keyLower = strtolower($key);
        foreach ($headers as $k => $v) {
            if (strtolower($k) === $keyLower) {
                return is_array($v) ? ($v[0] ?? null) : (string) $v;
            }
        }
        return null;
    }

    /**
     * Get browser name and version.
     */
    private static function getBrowserInfo(string $ua, ?string $chUa = null): array
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

        // Mobile Brand Browsers Popular in Indonesia (Samsung, Xiaomi, Tecno/Infinix, Oppo, Vivo, UC)
        if (preg_match('/SamsungBrowser\/([0-9\.]+)/i', $ua, $m)) {
            return ['name' => 'Samsung Internet', 'version' => $m[1]];
        }
        if (preg_match('/(?:MiuiBrowser|XiaoMi\/MiuiBrowser)\/([0-9\.]+)/i', $ua, $m)) {
            return ['name' => 'Miui Browser', 'version' => $m[1]];
        }
        if (preg_match('/(?:PHX|Phoenix)\/([0-9\.]+)/i', $ua, $m)) {
            return ['name' => 'Phoenix Browser', 'version' => $m[1]];
        }
        if (preg_match('/HeyTapBrowser\/([0-9\.]+)/i', $ua, $m)) {
            return ['name' => 'Oppo / HeyTap Browser', 'version' => $m[1]];
        }
        if (preg_match('/VivoBrowser\/([0-9\.]+)/i', $ua, $m)) {
            return ['name' => 'Vivo Browser', 'version' => $m[1]];
        }
        if (preg_match('/(?:UCBrowser|UCWEB)\/([0-9\.]+)/i', $ua, $m)) {
            return ['name' => 'UC Browser', 'version' => $m[1]];
        }

        // Major Desktop / Mobile Browsers (Edge, Opera, Chrome, Firefox, Safari)
        if (preg_match('/(?:Edg|Edge|EdgA|EdgiOS)\/([0-9\.]+)/i', $ua, $m)) {
            $name = 'Edge';
            $version = $m[1];
        } elseif (preg_match('/(?:OPR|Opera|OPT)\/([0-9\.]+)/i', $ua, $m)) {
            $name = 'Opera';
            $version = $m[1];
        } elseif (preg_match('/(?:Chrome|CriOS|CrMo|Chromium)\/([0-9\.]+)/i', $ua, $m)) {
            $name = 'Chrome';
            $version = $m[1];
        } elseif (preg_match('/(?:Chrome|CriOS|CrMo|Chromium)(?:\s+([0-9\.]+))?/i', $ua, $m)) {
            $name = 'Chrome';
            $version = $m[1] ?? null;
        } elseif (preg_match('/(?:Firefox|FxiOS|Focus)\/([0-9\.]+)/i', $ua, $m)) {
            $name = 'Firefox';
            $version = $m[1];
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

        // Fallback Client Hints check
        if ($name === 'Browser Direct / Unknown' && !empty($chUa)) {
            if (stripos($chUa, 'Google Chrome') !== false || stripos($chUa, 'Chrome') !== false) {
                $name = 'Chrome';
            } elseif (stripos($chUa, 'Chromium') !== false) {
                $name = 'Chromium';
            }
        }

        return ['name' => $name, 'version' => $version];
    }

    /**
     * Get Operating System name and version.
     */
    private static function getOsInfo(string $ua, ?string $chPlatform = null, ?string $chPlatformVersion = null): array
    {
        $name = 'Unknown OS';
        $version = null;

        // Android detection (handles 'Android 14', 'Android-14', 'Android/14', or standalone 'Android')
        if (preg_match('/Android(?:[\s\/\-_]?([0-9\.]+))?/i', $ua, $m)) {
            $name = 'Android';
            $version = !empty($m[1]) ? $m[1] : null;
        } elseif (preg_match('/Windows NT ([0-9\.]+)/i', $ua, $m)) {
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
        } elseif (preg_match('/(?:iPhone OS|CPU OS|iPad OS)\s*([0-9_]+)/i', $ua, $m)) {
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

        // Fallback: Check Client Hints platform
        if ($name === 'Unknown OS' && !empty($chPlatform)) {
            if (stripos($chPlatform, 'Android') !== false) {
                $name = 'Android';
                $version = $chPlatformVersion ?: null;
            } elseif (stripos($chPlatform, 'Windows') !== false) {
                $name = 'Windows';
            } elseif (stripos($chPlatform, 'iOS') !== false) {
                $name = 'iOS';
            } elseif (stripos($chPlatform, 'macOS') !== false) {
                $name = 'macOS';
            } elseif (stripos($chPlatform, 'Linux') !== false) {
                $name = 'Linux';
            }
        }

        return ['name' => $name, 'version' => $version];
    }

    /**
     * Detect if user agent is a search engine bot / crawler.
     */
    private static function isRobot(string $ua): bool
    {
        $robotsPattern = '/bot|crawl|slurp|spider|mediapartners|facebookexternalhit|twitterbot|lighthouse|googlebot|bingbot|yandexbot|duckduckbot|baiduspider|curl|postman|python|guzzle|wget|uptime|monitor|ping|probe|check|health|scanner|headless|phantom|selenium|puppeteer|playwright|httpclient|java|apache|go-http|okhttp|axios/i';
        return (bool) preg_match($robotsPattern, $ua);
    }

    /**
     * Get device category: Robot, Tablet, Mobile, or Desktop.
     */
    private static function getDeviceType(string $ua, bool $isRobot, string $osName = '', ?string $chMobile = null): string
    {
        if ($isRobot) {
            return 'Robot';
        }

        // Client Hints Mobile signal (?1 = true)
        if ($chMobile === '?1' || $chMobile === '1' || $chMobile === 'true') {
            return 'Mobile';
        }

        // Android phones are Mobile by default unless explicit tablet pattern
        if ($osName === 'Android' && !preg_match('/tablet|ipad|playbook|silk/i', $ua)) {
            return 'Mobile';
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
