<?php

namespace App\Jobs;

use App\Models\IpGeolocationCache;
use App\Models\VisitorGeolocation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessVisitorGeolocationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 10;

    protected int $visitorLogId;
    protected string $ipAddress;

    /**
     * Create a new job instance.
     */
    public function __construct(int $visitorLogId, string $ipAddress)
    {
        $this->visitorLogId = $visitorLogId;
        $this->ipAddress = $ipAddress;
    }

    /**
     * Execute the job in background queue.
     */
    public function handle(): void
    {
        $geoRecord = VisitorGeolocation::firstOrCreate(
            ['visitor_log_id' => $this->visitorLogId],
            ['status' => 'pending']
        );

        $ip = trim($this->ipAddress);

        // 1. Check IP Geolocation Cache
        $cached = IpGeolocationCache::where('ip', $ip)->first();
        if ($cached) {
            $geoRecord->update([
                'country'      => $cached->country,
                'country_code' => $cached->country_code,
                'province'     => $cached->province,
                'city'         => $cached->city,
                'district'     => $cached->district ?? '-',
                'isp'          => $cached->isp ?? '-',
                'asn'          => $cached->asn ?? '-',
                'confidence'   => $cached->confidence ?? 'medium',
                'ip_version'   => $cached->ip_version ?? (str_contains($ip, ':') ? 'IPv6' : 'IPv4'),
                'timezone'     => $cached->timezone,
                'latitude'     => $cached->latitude,
                'longitude'    => $cached->longitude,
                'status'       => 'completed',
                'error_message'=> null,
            ]);
            return;
        }

        // 2. Check if local / private IP address
        if ($this->isLocalOrPrivateIp($ip)) {
            $localData = [
                'ip'           => $ip,
                'country'      => 'Indonesia (Local)',
                'country_code' => 'ID',
                'province'     => 'Nusa Tenggara Barat',
                'city'         => 'Kabupaten Dompu (Estimasi)',
                'district'     => '-',
                'isp'          => 'Local Network / Loopback',
                'asn'          => 'LAN / Private IP',
                'confidence'   => 'high',
                'ip_version'   => str_contains($ip, ':') ? 'IPv6' : 'IPv4',
                'timezone'     => 'Asia/Makassar',
                'latitude'     => -8.5333000,
                'longitude'    => 118.4500000,
            ];

            IpGeolocationCache::updateOrCreate(['ip' => $ip], $localData);

            $geoRecord->update(array_merge($localData, ['status' => 'completed', 'error_message' => null]));
            return;
        }

        // 3. Query external Geolocation API (ip-api.com with 3s timeout)
        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,message,country,countryCode,regionName,city,district,zip,lat,lon,timezone,isp,org,as,mobile,proxy,hosting'
            ]);

            if ($response->successful() && $response->json('status') === 'success') {
                $data = $response->json();

                $isProxy = !empty($data['proxy']) || !empty($data['hosting']);
                $isMobile = !empty($data['mobile']);

                $confidence = 'medium';
                if ($isProxy) {
                    $confidence = 'very_low';
                } elseif ($isMobile) {
                    $confidence = 'low';
                }

                $geoData = [
                    'ip'           => $ip,
                    'country'      => $data['country'] ?? 'Unknown',
                    'country_code' => $data['countryCode'] ?? null,
                    'province'     => $data['regionName'] ?? 'Unknown',
                    'city'         => $data['city'] ?? 'Unknown',
                    'district'     => !empty($data['district']) ? $data['district'] : '-',
                    'isp'          => $data['isp'] ?? ($data['org'] ?? '-'),
                    'asn'          => $data['as'] ?? ($data['org'] ?? '-'),
                    'confidence'   => $confidence,
                    'ip_version'   => str_contains($ip, ':') ? 'IPv6' : 'IPv4',
                    'timezone'     => $data['timezone'] ?? null,
                    'latitude'     => $data['lat'] ?? null,
                    'longitude'    => $data['lon'] ?? null,
                ];

                IpGeolocationCache::updateOrCreate(['ip' => $ip], $geoData);

                $geoRecord->update(array_merge($geoData, [
                    'status'        => 'completed',
                    'error_message' => null,
                ]));
            } else {
                $geoRecord->update([
                    'status'        => 'failed',
                    'error_message' => $response->json('message') ?? 'API Geolocation returned non-success status.',
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("Geolocation lookup failed for IP {$ip}: " . $e->getMessage());
            $geoRecord->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if IP is local/private loopback address.
     */
    private function isLocalOrPrivateIp(string $ip): bool
    {
        if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return true;
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }
        return false;
    }
}
