<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpGeolocationCache extends Model
{
    protected $table = 'ip_geolocation_cache';

    protected $fillable = [
        'ip',
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
    ];
}
