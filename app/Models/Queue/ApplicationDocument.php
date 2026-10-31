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
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'is_compressed' => 'boolean',
        'file_size_bytes' => 'integer',
        'original_size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'reviewed_at' => 'datetime',
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

    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_PENDING => 'Belum Diperiksa',
            self::STATUS_APPROVED => 'Sudah Diperiksa',
            'VERIFIED' => 'Sudah Diperiksa',
            'EXAMINED' => 'Sudah Diperiksa',
            self::STATUS_REJECTED => 'Dokumen Ditolak',
            self::STATUS_REVISION_REQUIRED => 'Perlu Perbaikan',
        ][$this->status] ?? $this->status;
    }

    public function getAdminServeUrlAttribute(): string
    {
        return route('admin.queue.documents.serve', ['documentId' => $this->id]);
    }
}
