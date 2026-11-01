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
        'template_url',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'max_size_kb' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = ['accepted_formats_array', 'max_size_bytes', 'template_url'];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function applicationDocuments()
    {
        return $this->hasMany(ApplicationDocument::class, 'service_document_id');
    }

    public function getTemplateUrlAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        $label = strtolower($this->label ?? '');
        if (str_contains($label, 'f-1.06') || str_contains($label, 'f1.06') || str_contains($label, 'perubahan elemen')) {
            $download = \App\Models\Download::where('title', 'like', '%F1.06%')->orWhere('title', 'like', '%F-1.06%')->first();
            return $download ? url("/downloads/stream/{$download->id}") : 'https://dukcapil.dompukab.go.id/downloads';
        }
        if (str_contains($label, 'f-1.02') || str_contains($label, 'f1.02') || str_contains($label, 'pendaftaran peristiwa')) {
            return 'https://dukcapil.dompukab.go.id/downloads';
        }
        if (str_contains($label, 'kuasa') || str_contains($label, 'f-1.07') || str_contains($label, 'f.017')) {
            return 'https://dukcapil.dompukab.go.id/downloads';
        }

        return null;
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
