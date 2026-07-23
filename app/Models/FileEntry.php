<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class FileEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'folder_id',
        'name',
        'original_name',
        'path',
        'mime_type',
        'size',
        'extension',
        'disk',
        'version',
        'parent_version_id',
        'created_by',
    ];

    protected $appends = ['url', 'formatted_size'];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(FileFolder::class, 'folder_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parentVersion(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'parent_version_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FileEntry::class, 'parent_version_id')->orderBy('version', 'desc');
    }

    /**
     * Get the dynamic public URL for the file.
     */
    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
            return $this->path;
        }

        return '/storage/' . ltrim($this->path, '/');
    }

    /**
     * Format the file size in a human-readable string.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
