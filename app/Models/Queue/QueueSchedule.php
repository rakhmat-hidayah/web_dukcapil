<?php

namespace App\Models\Queue;

use Illuminate\Database\Eloquent\Model;

class QueueSchedule extends Model
{
    protected $table = 'queue_schedules';

    protected $fillable = [
        'schedule_date',
        'open_time',
        'close_time',
        'total_quota',
        'is_holiday',
        'holiday_note',
        'is_open',
    ];

    protected $casts = [
        'schedule_date' => 'date:Y-m-d',
        'is_holiday' => 'boolean',
        'is_open' => 'boolean',
        'total_quota' => 'integer',
    ];

    public function slots()
    {
        return $this->hasMany(QueueSlot::class, 'schedule_id');
    }

    public function isAvailable(): bool
    {
        return $this->is_open && !$this->is_holiday;
    }
}
