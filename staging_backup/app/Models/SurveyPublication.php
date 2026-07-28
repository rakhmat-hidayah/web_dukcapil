<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_period_id',
        'title',
        'summary',
        'published_at',
        'pdf_report_path',
        'excel_report_path',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(SurveyPeriod::class, 'survey_period_id');
    }
}
