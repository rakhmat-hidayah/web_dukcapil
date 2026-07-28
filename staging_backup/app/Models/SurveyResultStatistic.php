<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResultStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_period_id',
        'total_respondents',
        'average_score',
        'ikm_value',
        'service_quality_category',
        'index_breakdown_json',
    ];

    protected $casts = [
        'total_respondents' => 'integer',
        'average_score' => 'float',
        'ikm_value' => 'float',
        'index_breakdown_json' => 'array',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(SurveyPeriod::class, 'survey_period_id');
    }
}
