<?php

namespace App\Models\Queue;

use Illuminate\Database\Eloquent\Model;

class QueueSlot extends Model
{
    protected $table = 'queue_slots';

    protected $fillable = [
        'schedule_id',
        'service_type_id',
        'quota',
        'booked_count',
    ];

    protected $casts = [
        'quota' => 'integer',
        'booked_count' => 'integer',
    ];

    protected $appends = ['remaining_quota'];

    public function schedule()
    {
        return $this->belongsTo(QueueSchedule::class, 'schedule_id');
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function applications()
    {
        return $this->hasMany(QueueApplication::class, 'queue_slot_id');
    }

    public function getRemainingQuotaAttribute(): int
    {
        return max(0, $this->quota - $this->booked_count);
    }

    public function isAvailable(): bool
    {
        return $this->booked_count < $this->quota;
    }
}
