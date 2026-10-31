<?php

namespace App\Models\Queue;

use Illuminate\Database\Eloquent\Model;

class ServiceDocument extends Model
{
    protected $table = 'queue_service_documents';

    protected $fillable = [
        'service_type_id',
        'label',
        'description',
        'accepted_formats',
        'max_size_kb',
        'is_required',
        'capture_hint',
        'capture_mode',
        'applicable_to',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'max_size_kb' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = ['accepted_formats_array', 'max_size_bytes'];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function applicationDocuments()
    {
        return $this->hasMany(ApplicationDocument::class, 'service_document_id');
    }

    public function getAcceptedFormatsArrayAttribute(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', strtolower($this->accepted_formats ?? 'jpg,jpeg,png,pdf')))));
    }

    public function getMaxSizeBytesAttribute(): int
    {
        return ($this->max_size_kb ?: 5120) * 1024;
    }
}
