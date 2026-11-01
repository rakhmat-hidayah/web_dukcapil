<?php

namespace App\Models\Queue;

use Carbon\Carbon;
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
        'is_holiday'    => 'boolean',
        'is_open'       => 'boolean',
        'total_quota'   => 'integer',
    ];

    protected $appends = [
        'schedule_date_formatted',
        'schedule_date_indonesia',
        'operational_hours_formatted',
    ];

    /**
     * Indonesian format: "Senin, 02 November 2026"
     */
    public function getScheduleDateFormattedAttribute(): string
    {
        $raw = $this->getRawOriginal('schedule_date');
        if (!$raw) {
            return '-';
        }
        return Carbon::parse($raw)->locale('id')->translatedFormat('l, d F Y');
    }

    /**
     * Indonesian date format: "02/11/2026" (DD/MM/YYYY)
     */
    public function getScheduleDateIndonesiaAttribute(): string
    {
        $raw = $this->getRawOriginal('schedule_date');
        if (!$raw) {
            return '-';
        }
        return Carbon::parse($raw)->format('d/m/Y');
    }

    /**
     * Indonesian operational hours: "08:00 - 15:30 WITA"
     */
    public function getOperationalHoursFormattedAttribute(): string
    {
        if ($this->is_holiday) {
            return 'TUTUP (Hari Libur)';
        }
        $open = substr((string)$this->open_time, 0, 5) ?: '08:00';
        $close = substr((string)$this->close_time, 0, 5) ?: '15:30';
        return "{$open} - {$close} WITA";
    }

    public function slots()
    {
        return $this->hasMany(QueueSlot::class, 'schedule_id');
    }

    public function isAvailable(): bool
    {
        return $this->is_open && !$this->is_holiday;
    }
}
