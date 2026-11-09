<?php

namespace App\Models\Queue;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueRejectionTemplate extends Model
{
    protected $table = 'queue_rejection_templates';

    protected $fillable = [
        'title',
        'content',
        'type',
        'sort_order',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForRejection(Builder $query): Builder
    {
        return $query->whereIn('type', ['rejection', 'all']);
    }

    public function scopeForRevision(Builder $query): Builder
    {
        return $query->whereIn('type', ['revision', 'all']);
    }

    /**
     * Get all active templates ordered by sort_order.
     */
    public static function getActiveTemplates(?string $type = null): array
    {
        $query = static::active()->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        if ($type === 'rejection') {
            $query->forRejection();
        } elseif ($type === 'revision') {
            $query->forRevision();
        }
        return $query->get(['id', 'title', 'content', 'type', 'sort_order'])->toArray();
    }
}
