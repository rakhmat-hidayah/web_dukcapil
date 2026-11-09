<?php

namespace App\Models\Queue;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ApplicationDocument extends Model
{
    protected $table = 'queue_application_documents';

    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_REVISION_REQUIRED = 'REVISION_REQUIRED';

    protected $fillable = [
        'application_id',
        'service_document_id',
        'original_filename',
        'stored_filename',
        'disk',
        'path',
        'mime_type',
        'file_size_bytes',
        'width',
        'height',
        'is_compressed',
        'original_size_bytes',
        'status',
        'reviewer_note',
        'is_destroyed',
        'destroyed_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'is_compressed' => 'boolean',
        'is_destroyed' => 'boolean',
        'file_size_bytes' => 'integer',
        'original_size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'reviewed_at' => 'datetime',
        'destroyed_at' => 'datetime',
    ];

    protected $hidden = [
        'path',
        'stored_filename',
        'disk',
    ];

    protected $appends = ['formatted_size', 'is_image', 'status_label', 'admin_serve_url'];

    public function application()
    {
        return $this->belongsTo(QueueApplication::class, 'application_id');
    }

    public function serviceDocument()
    {
        return $this->belongsTo(ServiceDocument::class, 'service_document_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size_bytes;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isSelfie(): bool
    {
        $serviceDoc = $this->serviceDocument;
        if ($serviceDoc) {
            $label = strtolower($serviceDoc->label ?? '');
            if ($serviceDoc->capture_mode === 'user'
                || str_contains($label, 'selfie')
                || str_contains($label, 'foto wajah')
                || str_contains($label, 'foto pemohon')
                || str_contains($label, 'pas foto')
                || str_contains($label, 'foto diri')) {
                return true;
            }
        }
        $filename = strtolower($this->original_filename ?? '');
        return str_contains($filename, 'selfie')
            || str_contains($filename, 'foto_wajah')
            || str_contains($filename, 'foto-wajah')
            || str_contains($filename, 'pas_foto')
            || str_contains($filename, 'foto_pemohon');
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_destroyed) {
            return 'Foto Dimusnahkan';
        }

        return [
            self::STATUS_PENDING => 'Belum Diperiksa',
            self::STATUS_APPROVED => 'Sudah Diperiksa',
            'VERIFIED' => 'Sudah Diperiksa',
            'EXAMINED' => 'Sudah Diperiksa',
            self::STATUS_REJECTED => 'Dokumen Ditolak',
            self::STATUS_REVISION_REQUIRED => 'Perlu Perbaikan',
        ][$this->status] ?? $this->status;
    }

    public function getAdminServeUrlAttribute(): ?string
    {
        if ($this->is_destroyed || empty($this->getRawOriginal('path'))) {
            return null;
        }

        return route('admin.queue.documents.serve', ['documentId' => $this->id]);
    }
}
