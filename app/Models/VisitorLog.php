<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class VisitorLog extends Model
{
    protected $fillable = [
        'visitor_uuid',
        'session_id',
        'ip_address',
        'url',
        'method',
        'user_agent',
        'browser',
        'browser_version',
        'operating_system',
        'operating_system_version',
        'device_type',
        'device',
        'platform',
        'is_robot',
        'is_human',
        'visitor_category',
        'bot_name',
        'detection_reason',
        'referrer',
        'visited_at',
        'download_file_id',
        'user_id',
    ];

    protected $casts = [
        'is_robot' => 'boolean',
        'is_human' => 'boolean',
        'visited_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function downloadFile(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'download_file_id');
    }

    public function geolocation(): HasOne
    {
        return $this->hasOne(VisitorGeolocation::class, 'visitor_log_id');
    }

    /**
     * Mask IP address for privacy in public tables (e.g., 103.145.xxx.xxx).
     */
    public function getMaskedIpAttribute(): string
    {
        $ip = $this->ip_address;
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                return $parts[0] . '.' . $parts[1] . '.xxx.xxx';
            }
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            if (count($parts) >= 2) {
                return $parts[0] . ':' . $parts[1] . ':*:*';
            }
        }
        return 'xxx.xxx.xxx.xxx';
    }

    /**
     * Scope for today's logs.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for online visitors (active in last 5 minutes).
     */
    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subMinutes(5));
    }

    /**
     * Scope for Human visitors only.
     */
    public function scopeHuman(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('is_human', true)
              ->orWhere('visitor_category', 'human');
        });
    }

    /**
     * Scope for Bot visitors (Search Engine, AI, Social, SEO, Monitoring, Unknown).
     */
    public function scopeBots(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('is_human', false)
              ->orWhere('is_robot', true);
        })->where('visitor_category', '!=', 'security_scanner');
    }

    /**
     * Scope for Security Scanners / Probes.
     */
    public function scopeScanners(Builder $query): Builder
    {
        return $query->where('visitor_category', 'security_scanner');
    }
}
