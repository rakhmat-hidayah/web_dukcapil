<?php

namespace App\Models\Archive;

use App\Models\Queue\QueueApplication;
use Illuminate\Database\Eloquent\Model;

class ArchivePackageItem extends Model
{
    protected $table = 'archive_package_items';

    protected $fillable = [
        'archive_package_id',
        'application_id',
        'reg_number',
        'applicant_name_masked',
        'document_count',
        'total_size_bytes',
        'status',
        'notes',
    ];

    protected $casts = [
        'document_count' => 'integer',
        'total_size_bytes' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(ArchivePackage::class, 'archive_package_id');
    }

    public function application()
    {
        return $this->belongsTo(QueueApplication::class, 'application_id');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->total_size_bytes;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }
}
