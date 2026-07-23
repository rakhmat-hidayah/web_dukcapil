<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyFollowUpAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_period_id',
        'recommendation_id',
        'action_name',
        'description',
        'responsible_unit',
        'progress',
        'evidence_document',
        'completion_date',
        'status',
    ];

    protected $casts = [
        'progress' => 'integer',
        'completion_date' => 'date',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(SurveyPeriod::class, 'survey_period_id');
    }

    public function recommendation(): BelongsTo
    {
        return $this->belongsTo(SurveyRecommendation::class, 'recommendation_id');
    }
}
