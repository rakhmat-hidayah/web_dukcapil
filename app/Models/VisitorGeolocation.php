<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorGeolocation extends Model
{
    protected $table = 'visitor_geolocations';

    protected $fillable = [
        'visitor_log_id',
        'country',
        'country_code',
        'province',
        'city',
        'district',
        'isp',
        'asn',
        'confidence',
        'ip_version',
        'timezone',
        'latitude',
        'longitude',
        'status',
        'error_message',
    ];

    public function visitorLog(): BelongsTo
    {
        return $this->belongsTo(VisitorLog::class, 'visitor_log_id');
    }
}
