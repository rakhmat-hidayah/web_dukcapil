<?php

namespace App\Models\Queue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceType extends Model
{
    protected $table = 'queue_service_types';

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'long_description',
        'icon',
        'color',
        'quota_per_day',
        'processing_days_label',
        'cost_label',
        'requires_face_photo',
        'allow_self',
        'allow_family_in_kk',
        'allow_family_out_kk',
        'allow_other',
        'require_power_of_attorney',
        'require_absence_reason',
        'is_biometric_first_recording',
        'policy_notes',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_face_photo' => 'boolean',
        'allow_self' => 'boolean',
        'allow_family_in_kk' => 'boolean',
        'allow_family_out_kk' => 'boolean',
        'allow_other' => 'boolean',
        'require_power_of_attorney' => 'boolean',
        'require_absence_reason' => 'boolean',
        'is_biometric_first_recording' => 'boolean',
        'quota_per_day' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->name);
            }
        });

        static::updating(function ($m) {
            if ($m->isDirty('name') && empty($m->slug)) {
                $m->slug = Str::slug($m->name);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function documents()
    {
        return $this->hasMany(ServiceDocument::class, 'service_type_id')->orderBy('sort_order');
    }

    public function serviceDocuments()
    {
        return $this->documents();
    }

    public function requiredDocuments()
    {
        return $this->documents()->where('is_required', true);
    }

    public function slots()
    {
        return $this->hasMany(QueueSlot::class, 'service_type_id');
    }

    public function applications()
    {
        return $this->hasMany(QueueApplication::class, 'service_type_id');
    }
}
