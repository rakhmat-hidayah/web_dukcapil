<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_period_id',
        'title',
        'description',
        'priority',
        'status',
        'target_completion',
        'pic',
    ];

    protected $casts = [
        'target_completion' => 'date',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(SurveyPeriod::class, 'survey_period_id');
    }

    public function followUpActions(): HasMany
    {
        return $this->hasMany(SurveyFollowUpAction::class, 'recommendation_id');
    }
}
