<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Download extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'download_category_id', 'title', 'description',
        'file_path', 'file_name', 'file_type', 'file_size',
        'document_number', 'document_date', 'status', 'published_at',
    ];

    protected $casts = [
        'document_date' => 'date',
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DownloadCategory::class, 'download_category_id');
    }

    public function fileSizeFormatted(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
