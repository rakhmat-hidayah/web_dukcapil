<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SurveyPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'semester',
        'year',
        'start_date',
        'end_date',
        'status',
        'is_active',
        'target_respondents',
        'created_by',
        'survey_mechanism',
        'external_survey_url',
        'external_ikm_value',
        'external_service_quality',
        'external_total_respondents',
        'external_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'target_respondents' => 'integer',
        'external_ikm_value' => 'float',
        'external_total_respondents' => 'integer',
    ];

    public function isExternalPanRb(): bool
    {
        return $this->survey_mechanism === 'panrb';
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('sort_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function statistic(): HasOne
    {
        return $this->hasOne(SurveyResultStatistic::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(SurveyRecommendation::class);
    }

    public function followUpActions(): HasMany
    {
        return $this->hasMany(SurveyFollowUpAction::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(SurveyPublication::class);
    }
}
